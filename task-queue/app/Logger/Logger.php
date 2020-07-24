<?php

namespace App\Logger;

use App\AbstractAMQPWorker;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Logger extends AbstractAMQPWorker
{
    /**
     * @var string
     */
    private string $queueLogName;

    /**
     * AbstractAMQPWorkerWithLog constructor.
     *
     * @param \PhpAmqpLib\Connection\AMQPStreamConnection $connection
     * @param array                                       $options
     */
    public function __construct(AMQPStreamConnection $connection, array $options)
    {
        parent::__construct($connection);
        $this->queueLogName = $options['queueLogName'];
    }

    /**
     *
     */
    public function init(): void
    {
        $this->channel->queue_declare($this->queueLogName, false, true, false, false);
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
        $this->channel->basic_consume($this->queueLogName, '', false, false, false, false, function (AMQPMessage $message) {
            echo microtime() . ' ' . $message->body . PHP_EOL;
            $this->ack($message);
        });
    }
}
