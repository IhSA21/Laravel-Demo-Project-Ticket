<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPConsumerQOS
{
    public $connection;
    public $channel;

    public function __construct(string $host, int $port, string $username, string $password, string $vhost)
    {
        $this->connection = new AMQPStreamConnection($host, $port, $username, $password, $vhost);
        $this->channel = $this->connection->channel();
    }

    public function publish(string $message, string $exchangeName, int $qosPrefetchCount = 0)
    {
        $this->channel->exchange_declare($exchangeName, 'fanout', false, false, true);
        $this->channel->basic_qos(0, $qosPrefetchCount, false);
        $message = new AMQPMessage($message);
        $this->channel->basic_publish($message, $exchangeName);
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
