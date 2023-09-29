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
     * Открывает соедиение
     */
    public function __construct() {
        $this->connection = RabbitMQConnection::getInstance()->getConnection(); //Получаем и открываем соединение
        $this->channel = $this->connection->channel(); //получаем объект channel
        $this->messageHandler = new MessageHandler(); //наш обработчик сообщений
        $this->consumer = new Consumer($this->channel); //создаёт обработчик очереди

        register_shutdown_function([$this, 'closeConnections']); //Этот метод будет запускаться в случае закрытия демона
    }

    /**
     * Начать обработку очереди
     * @return void
     */
    public function processQueue() {
        $queueName = Esb::getValue('AMQP_INPUT');
        $this->channel->queue_declare($queueName, false, true, false, false); //Определяет очередь на уровне rabbitmq
        $this->channel->basic_qos(0, 20, false); //Задаёт количество сообщений, которое забирать из очереди за 1 раз
        $this->consumer->consume($queueName, [$this->messageHandler, 'handleMessage']); //Обрабатывает очередь методом handleMessage
        $this->closeConnections();
    }

    /**
     * Закрыть соединение
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