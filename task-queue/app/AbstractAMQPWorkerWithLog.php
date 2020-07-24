<?php

namespace App;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;

abstract class AbstractAMQPWorkerWithLog extends AbstractAMQPWorker
{
    /**
     * @var string
     */
    private string $exchangeLogName;

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
        $this->exchangeLogName = $options['exchangeLogName'];
        $this->queueLogName = $options['queueLogName'];
    }

    /**
     *
     */
    public function init(): void
    {
        $this->channel->exchange_declare($this->exchangeLogName, AMQPExchangeType::DIRECT, false, true, false);
        $this->channel->queue_declare($this->queueLogName, false, true, false, false);
        $this->channel->queue_bind($this->queueLogName, $this->exchangeLogName);
    }

    /**
     * @param array $data
     */
    protected function log(array $data): void
    {
        $this->channel->basic_publish($this->makeMessage($data), $this->exchangeLogName);
    }
}
