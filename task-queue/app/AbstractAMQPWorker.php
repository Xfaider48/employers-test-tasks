<?php

namespace App;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

abstract class AbstractAMQPWorker
{
    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    protected AMQPChannel $channel;

    /**
     * AbstractAMQPWorker constructor.
     *
     * @param \PhpAmqpLib\Connection\AMQPStreamConnection $connection
     */
    public function __construct(AMQPStreamConnection $connection)
    {
        $this->channel = $connection->channel();
    }

    /**
     *
     */
    abstract public function init(): void;

    /**
     * @param array $data
     *
     * @return \PhpAmqpLib\Message\AMQPMessage
     */
    protected function makeMessage(array $data): AMQPMessage
    {
        return new AMQPMessage(json_encode($data));
    }

    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     */
    protected function ack(AMQPMessage $message): void
    {
        $this->channel->basic_ack($message->getDeliveryTag());
    }

    /**
     * @param \PhpAmqpLib\Message\AMQPMessage $message
     * @param bool                            $requeue
     */
    protected function nack(AMQPMessage $message, bool $requeue = false): void
    {
        $this->channel->basic_reject($message->getDeliveryTag(), $requeue);
    }

    /**
     * @param int $workerNumber
     *
     * @return string
     */
    protected function getWorkerQueueName(int $workerNumber): string
    {
        return 'worker-' . $workerNumber;
    }

    /**
     * @param int $workerNumber
     *
     * @return array
     */
    protected function initWorkerQueue(int $workerNumber): array
    {
        $queue = $this->getWorkerQueueName($workerNumber);
        return $this->channel->queue_declare($queue, false, true, false, false);
    }
}
