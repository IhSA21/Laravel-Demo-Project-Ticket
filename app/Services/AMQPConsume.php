<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;

class AMQPConsume
{
    private $exchange;
    private $queue;
    private $consumerTag;
    private $connection;
    private $channel;

    public function __construct($exchange, $queue, $consumerTag)
    {
        $this->exchange = $exchange;
        $this->queue = $queue;
        $this->consumerTag = $consumerTag;
        $this->connection = new AMQPStreamConnection('172.24.2.4', 5672, 'rabbitmq', 'rabbitmq', '/');
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare($this->queue, false, true, false, false);
        $this->channel->exchange_declare($this->exchange, AMQPExchangeType::FANOUT, false, false, true);
        $this->channel->queue_bind($this->queue, $this->exchange);
    }
    public function processMessage($message)
    {
        $payload = json_decode(true);
        if (isset($payload) && isset($payload['message'])) {
            echo "\n--------\n";
            echo $payload['message'];
            echo "\n--------\n";
        }
        $message->ack();
    }

    public function consume()
    {
        $this->channel->basic_consume($this->queue, $this->consumerTag, false, false, false, false, function ($message) {
            $this->processMessage($message);
        });

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    public function wait()
    {
        while ($this->channel->is_consuming()) {
            $this->channel->wait(null, false, 10);
        }
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
