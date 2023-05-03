<?php

use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Message\AMQPMessage;

return [
    /**
     * Default connection
     */
    'default' => env('AMQP_CONNECTION', 'rabbitmq'),

    /**
     * Available connections
     */
    'connections' => [
        'rabbitmq' => [
            'connection' => [
                /**
                 * Lazy connection does not support more than 1 host
                 * Change connection **class** if you want to try more than one host
                 */
                'class' => AMQPSSLConnection::class,
                'hosts' => [
                    [
                        'host' => env('AMQP_HOST', '192.168.1.10'),
                        'port' => env('AMQP_PORT', 5672),
                        'user' => env('AMQP_USER', 'rabbitmq'),
                        'password' => env('AMQP_PASSWORD', 'rabbitmq'),
                        'vhost' => env('AMQP_VHOST', '/'),
                    ],
                ],
                /**
                 * Pass additional options that are required for the AMQP*Connection class
                 * You can check *Connection::try_create_connection method to check
                 * if you want to pass additional data
                 */
                'options' => [],
            ],

            'message' => [
                'content_type' => env('AMQP_MESSAGE_CONTENT_TYPE', 'text/plain'),
                'delivery_mode' => env('AMQP_MESSAGE_DELIVERY_MODE', AMQPMessage::DELIVERY_MODE_PERSISTENT),
                'content_encoding' => env('AMQP_MESSAGE_CONTENT_ENCODING', 'UTF-8'),
            ],
        ],
    ],
];
