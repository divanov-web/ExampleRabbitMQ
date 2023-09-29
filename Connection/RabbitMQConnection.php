<?php

namespace aton\tools\Exchange\RabbitMQ\Connection;

use aton\tools\Settings\Esb;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQConnection implements ConnectionInterface
{
    private static $instance = null;
    private $connection;

    /**
     * Устанавливает соединение с RabbitMQ.
     * @throws \Exception
     */
    private function __construct()
    {
        $hosts = Esb::getValue('AMQP_HOST');
        $port = Esb::getValue('AMQP_PORT');
        $user = Esb::getValue('AMQP_USER');
        $password = Esb::getValue('AMQP_PASS');
        $arConfig = [];
        foreach (explode(";", $hosts) as $host) {
            if ($host)
                $arConfig[] = ['host' => $host, 'port' => $port, 'user' => $user, 'password' => $password, 'vhost' => "/"];
        }
        $this->connection = AMQPStreamConnection::create_connection($arConfig);
    }

    /**
     * Singleton
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection;
    }
}