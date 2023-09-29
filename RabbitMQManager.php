<?php

namespace aton\tools\Exchange\RabbitMQ;

use aton\tools\Exchange\RabbitMQ\Connection\RabbitMQConnection;
use aton\tools\Exchange\RabbitMQ\Consumer\Consumer;
use aton\tools\Exchange\RabbitMQ\Consumer\MessageHandler;
use aton\tools\Settings\Esb;

class RabbitMQManager {
    private $connection;
    private $channel;
    private $consumer;
    private $messageHandler;

    /**
     * open connection
     */
    public function __construct() {
        $this->connection = RabbitMQConnection::getInstance()->getConnection(); //open and get connection
        $this->channel = $this->connection->channel(); //initialize channel
        $this->messageHandler = new MessageHandler(); //our message handler
        $this->consumer = new Consumer($this->channel); //initialize consumer

        register_shutdown_function([$this, 'closeConnections']); //runs this method if connection is being closed
    }

    /**
     * start consume
     * @return void
     */
    public function processQueue() {
        $queueName = Esb::getValue('AMQP_INPUT');
        $this->channel->queue_declare($queueName, false, true, false, false);
        $this->channel->basic_qos(0, 20, false); //message count per time
        $this->consumer->consume($queueName, [$this->messageHandler, 'handleMessage']); //consume with handleMessage method
        $this->closeConnections();
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function closeConnections() {
        echo "Close connection" . PHP_EOL;
        if ($this->channel && $this->channel->is_open()) {
            $this->channel->close();
        }
        if ($this->connection && $this->connection->isConnected()) {
            $this->connection->close();
        }
    }
}