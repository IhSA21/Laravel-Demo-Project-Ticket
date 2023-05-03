<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AMQPConsume;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;

class ConsumeAMQPCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'amqp:consume';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume messages from RabbitMQ';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $exchange = 'Test-2';
        $queue = 'Test-2';
        $consumerTag = 'Consumer';
        $consumer = new AMQPConsume($exchange, $queue, $consumerTag);
        $consumer->consume();
        $consumer->wait();

        return 0;
    }
}
