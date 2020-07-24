<?php

namespace App\Publisher;

use App\AbstractAMQPWorkerWithLog;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;

class Producer extends AbstractAMQPWorkerWithLog
{
    /**
     * @var string
     */
    private string $eventExchangeName;

    /**
     * @var string
     */
    private string $eventQueueName;

    /**
     * Worker constructor.
     *
     * @param \PhpAmqpLib\Connection\AMQPStreamConnection $connection
     * @param array                                       $options
     */
    public function __construct(AMQPStreamConnection $connection, array $options)
    {
        parent::__construct($connection, $options);
        $this->eventQueueName = $options['eventQueueName'];
        $this->eventExchangeName = $options['eventExchangeName'];
    }

    /**
     *
     */
    public function init(): void
    {
        parent::init();
        $this->channel->exchange_declare($this->eventExchangeName, AMQPExchangeType::DIRECT, false, true, false);
        $this->channel->queue_declare($this->eventQueueName, false, true, false, false);
        $this->channel->queue_bind($this->eventQueueName, $this->eventExchangeName);
    }

    /**
     * @param array $event
     */
    public function publish(array $event): void
    {
        $syncId = $this->getEventSyncId($event);
        $this->log([
            'message' => 'Producer: Publishing event',
            'data' => $event
        ]);

        $this->publishEvent($syncId, $event);
    }

    /**
     * @param array $event
     *
     * @return string
     */
    protected function getEventSyncId(array $event): string
    {
        return $event['channel'] . '_' . $event['clientId'];
    }

    /**
     * @param string $syncId
     * @param array  $event
     */
    protected function publishEvent(string $syncId, array $event): void
    {
        $message = $this->makeMessage(compact('syncId', 'event'));
        $this->channel->basic_publish($message, $this->eventExchangeName);
    }
}
