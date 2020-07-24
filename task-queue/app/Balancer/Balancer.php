<?php

namespace App\Balancer;

use App\AbstractAMQPWorkerWithLog;
use Illuminate\Database\Connection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class Balancer extends AbstractAMQPWorkerWithLog
{
    /**
     * @var \Illuminate\Database\Connection
     */
    private Connection $dbConnection;

    /**
     * @var string
     */
    private string $eventQueueName;

    /**
     * @var string
     */
    private string $eventExchangeName;

    /**
     * @var string
     */
    private string $workerExchangeName;

    /**
     * @var int
     */
    private int $numberOfWorkers;

    /**
     * @var array
     */
    private array $syncIdToWorkerMap = [];

    /**
     * @var array
     */
    private array $workerToSyncIdMap = [];

    /**
     * @var int
     */
    private int $currentWorkerPointer = 0;

    /**
     * Balancer constructor.
     *
     * @param \Illuminate\Database\Connection             $dbConnection
     * @param \PhpAmqpLib\Connection\AMQPStreamConnection $connection
     * @param array                                       $options
     */
    public function __construct(Connection $dbConnection, AMQPStreamConnection $connection, array $options)
    {
        parent::__construct($connection, $options);
        $this->dbConnection = $dbConnection;
        $this->eventQueueName = $options['eventQueueName'];
        $this->eventExchangeName = $options['eventExchangeName'];
        $this->workerExchangeName = $options['workerExchangeName'];
        $this->numberOfWorkers = $options['numberOfWorkers'];
    }

    /**
     *
     */
    public function init(): void
    {
        parent::init();
        $this->channel->exchange_declare($this->workerExchangeName, AMQPExchangeType::TOPIC, false, true, false);
        $this->channel->exchange_declare($this->eventExchangeName, AMQPExchangeType::DIRECT, false, true, false);
        $this->channel->queue_declare($this->eventQueueName, false, true, false, false);
        $this->channel->queue_bind($this->eventQueueName, $this->eventExchangeName);
    }

    /**
     * @throws \ErrorException
     */
    public function start()
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
        $this->channel->basic_consume($this->eventQueueName, '', false, false, false, false, function (AMQPMessage $message) {
            try {
                $this->consumeEvent($message);
            } catch (\Throwable $e) {
                $this->nack($message, true);
                $this->log([
                    'message' => 'Balancer: Error event consumption',
                    'error' => $e->getMessage()
                ]);
            }
        });
    }

    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     */
    protected function consumeEvent(AMQPMessage $message): void
    {
        $eventData = json_decode($message->body, true);
        $syncId = $eventData['syncId'];

        $workerNumber = $this->tryGetCurrentWorkerBySyncId($syncId);
        if ($workerNumber === null) {
            while (true) {
                if ($this->isFreeLock($syncId)) {
                    $workerNumber = $this->getFreeWorker();
                    break;
                }
            }
        }

        $this->log([
            'message' => 'Balancer: Publishing event to worker',
            'workerNumber' => $workerNumber,
            'event' => $eventData
        ]);
        $this->publishEvent($workerNumber, $eventData);
        $this->ack($message);

        if (isset($this->syncIdToWorkerMap[$syncId]) && $this->syncIdToWorkerMap[$syncId] !== $workerNumber) {
            $op = 1;
        }

        $this->syncIdToWorkerMap[$syncId] = $workerNumber;
        $this->workerToSyncIdMap[$workerNumber] = $syncId;
    }

    /**
     * @param int $workerNumber
     *
     * @return bool
     */
    protected function workerIsFree(int $workerNumber): bool
    {
        $workerQueueData = $this->initWorkerQueue($workerNumber);
        if ($workerQueueData[1] === 0) {
            if (isset($this->workerToSyncIdMap[$workerNumber])) {
                $syncId = $this->workerToSyncIdMap[$workerNumber];
                if ($this->isFreeLock($syncId)) {
                    unset($this->syncIdToWorkerMap[$syncId]);
                    unset($this->workerToSyncIdMap[$workerNumber]);
                    return true;
                }

                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    protected function getFreeWorker(): int
    {
        while (true) {
            if ($this->workerIsFree($this->currentWorkerPointer)) {
                $workerNumber = $this->currentWorkerPointer;
                $this->nextWorkerPointer();
                return $workerNumber;
            }

            $this->nextWorkerPointer();
        }
    }

    /**
     * @return int
     */
    protected function nextWorkerPointer(): int
    {
        $this->currentWorkerPointer++;
        if ($this->currentWorkerPointer >= $this->numberOfWorkers) {
            $this->currentWorkerPointer = 0;
        }

        return $this->currentWorkerPointer;
    }

    /**
     * @param string $syncId
     *
     * @return int|null
     */
    protected function tryGetCurrentWorkerBySyncId(string $syncId): ?int
    {
        if (isset($this->syncIdToWorkerMap[$syncId])) {
            $workerNumber = $this->syncIdToWorkerMap[$syncId];
            $stats = $this->initWorkerQueue($workerNumber);
            if ($stats[1] !== 0) {
                return $workerNumber;
            } else {
                if ($this->isFreeLock($syncId)) {
                    unset($this->syncIdToWorkerMap[$syncId]);
                    unset($this->workerToSyncIdMap[$workerNumber]);
                } else {
                    return $workerNumber;
                }
            }
        }

        return null;
    }

    /**
     * @param int   $workerNumber
     * @param array $event
     */
    protected function publishEvent(int $workerNumber, array $event): void
    {
        $workerRouteKey = $this->getWorkerQueueName($workerNumber);
        $message = $this->makeMessage($event);
        $this->channel->basic_publish($message, $this->workerExchangeName, $workerRouteKey);
    }

    /**
     * @param string $lockName
     *
     * @return bool
     */
    protected function isFreeLock(string $lockName): bool
    {
        $result = $this->dbConnection->select('SELECT IS_FREE_LOCK("' . $lockName . '") "lock"');
        $first = current($result);
        return (bool)(int)$first->lock;
    }
}
