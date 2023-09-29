<?php

namespace aton\tools\Exchange\RabbitMQ\Consumer;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class Consumer {
    private AMQPChannel $channel;

    public function __construct(AMQPChannel $channel) {
        $this->channel = $channel;
    }

    /**
     * @param string $queueName
     * @param callable $callback
     * @return void
     */
    public function consume(string $queueName, callable $callback): void {
        $this->channel->basic_consume(
            $queueName,
            '',
            false,
            false,
            false,
            false,
            $callback
        );

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }
}