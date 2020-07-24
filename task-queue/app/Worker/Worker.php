<?php

namespace App\Worker;

use App\AbstractAMQPWorkerWithLog;
use Illuminate\Database\Connection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class Worker extends AbstractAMQPWorkerWithLog
{
    private const QUEUE_PREFIX_NAME = 'worker-';

    /**
     * @var \Illuminate\Database\Connection
     */
    private Connection $dbConnection;

    /**
     * @var int
     */
    private int $number;

    /**
     * @var string
     */
    private string $queueName;

    /**
     * @var string
     */
    private string $workerExchangeName;

    /**
     * @var string
     */
    private ?string $lastSyncId = null;

    /**
     * Worker constructor.
     *
     * @param \Illuminate\Database\Connection             $dbConnection
     * @param \PhpAmqpLib\Connection\AMQPStreamConnection $connection
     * @param array                                       $options
     */
    public function __construct(Connection $dbConnection, AMQPStreamConnection $connection, array $options)
    {
        parent::__construct($connection, $options);
        $this->dbConnection = $dbConnection;
        $this->workerExchangeName = $options['workerExchangeName'];
        $this->number = (int)$options['number'];
        $this->queueName = static::QUEUE_PREFIX_NAME . $this->number;
    }

    /**
     *
     */
    public function init(): void
    {
        parent::init();
        $this->channel->exchange_declare($this->workerExchangeName, AMQPExchangeType::TOPIC, false, true, false);
        $this->channel->queue_declare($this->queueName, false, true, false, false);
        $this->channel->queue_bind($this->queueName, $this->workerExchangeName, $this->queueName);
    }

    /**
     * @throws \ErrorException
     */
    public function start(): void
    {
        $this->initConsumption();
        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    /**
     *
     */
    protected function initConsumption(): void
    {
        $this->channel->basic_qos(null, 1, false);
        $this->channel->basic_consume($this->queueName, '', false, false, false, false, function (AMQPMessage $message) {
            try {
                $this->consumeEvent($message);
            } catch (\Throwable $e) {
                $this->nack($message, true);
                $this->log([
                    'message' => 'Worker: Error event consumption',
                    'error' => $e->getMessage()
                ]);

                sleep(1);
            }
        });
    }

    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     *
     * @throws \Throwable
     */
    protected function consumeEvent(AMQPMessage $message): void
    {
        $eventData = json_decode($message->body, true);
        $syncId = $eventData['syncId'];

        if ($this->lastSyncId === null || $this->lastSyncId !== $syncId) {
            $this->releaseLocks();
            while (true) {
                if ($this->tryLock($syncId)) {
                    $this->lastSyncId = $syncId;
                    break;
                } else {
                    usleep(rand(0, 10));
                }
            }
        }

        $this->handleEvent($eventData['event']);
        $this->ack($message);

        $queueData = $this->initWorkerQueue($this->number);
        if ($queueData[1] === 0) {
            $this->lastSyncId = null;
            $this->releaseLocks();
        }
    }

    /**
     * @param array $event
     */
    protected function handleEvent(array $event): void
    {
        $this->log([
            'message' => 'Worker: Handling event',
            'event' => $event
        ]);

        // Do some work
        sleep(1);

        $this->log([
            'message' => 'Worker: Event handled',
            'event' => $event
        ]);
    }

    /**
     * @param string $lockName
     *
     * @return bool
     */
    protected function tryLock(string $lockName): bool
    {
        $result = $this->dbConnection->select('SELECT GET_LOCK("' . $lockName . '", 0) as "lock"');
        $first = current($result);
        return (bool)(int)$first->lock;
    }

    /**
     *
     */
    protected function releaseLocks(): void
    {
        $this->dbConnection->select('SELECT RELEASE_ALL_LOCKS()');
    }
}
