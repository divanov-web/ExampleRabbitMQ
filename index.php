<?php

use aton\tools\Exchange\RabbitMQ\RabbitMQManager;

require_once Configuration::getValue("vendor_path");

echo "start rabbitMQ manager" . PHP_EOL;

$rabbitManager = new RabbitMQManager();
$rabbitManager->processQueue();
