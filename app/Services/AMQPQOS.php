<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPQOS
{
    protected $connection;
    protected $channel;

    public function __construct(string $host, int $port, string $user, string $pass, string $vhost)
    {
        $this->connection = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
        $this->channel = $this->connection->channel();
    }

    public function setAckHandler(callable $callback)
    {
        $this->channel->set_ack_handler($callback);
    }

    public function setNackHandler(callable $callback)
    {
        $this->channel->set_nack_handler($callback);
    }

    public function confirmSelect()
    {
        $this->channel->confirm_select();
    }

    public function exchangeDeclare(string $exchange, string $type, bool $passive = false, bool $durable = false, bool $auto_delete = true)
    {
        $this->channel->exchange_declare($exchange, $type, $passive, $durable, $auto_delete);
    }

    public function queueDeclare(string $queue, bool $passive, bool $durable, bool $exclusive, bool $auto_delete, bool $nowait, bool $arguments = null)
    {
        $this->channel->queue_declare($queue, $passive, $durable, $exclusive, $auto_delete, $nowait);
    }

    public function queueBind(string $queue, string $exchange)
    {
        $this->channel->queue_bind($queue, $exchange);
    }

    public function basicPublish(AMQPMessage $msg, string $exchange, string $message, int $qosPrefetchCount = 0)
    {
        $this->channel->basic_qos(0, $qosPrefetchCount, false);
        $this->channel->basic_publish($msg, $message, $exchange);
    }

    public function setConsumerPrefetchCount(bool $prefetchSize, int $prefetchCount = 0, bool $global)
    {
        $this->channel->basic_qos($prefetchSize, $prefetchCount, $global);
    }


    public function waitForPendingAcks()
    {
        $this->channel->wait_for_pending_acks();
    }

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }
}