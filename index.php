<?php
ini_set("max_execution_time", 0);
ini_set("max_input_time", 0);
ini_set('memory_limit', '64M');
set_time_limit(0);

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_BUFFER_USED", true);

$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/ext_www/amaton/build";
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Config\Configuration;
use aton\tools\Exchange\RabbitMQ\RabbitMQManager;

require_once Configuration::getValue("vendor_path");

echo "start rabbitMQ manager" . PHP_EOL;

$rabbitManager = new RabbitMQManager();
$rabbitManager->processQueue();

//на случай ,если нам понадобиться создавать PID
/*$pid = getmypid();
file_put_contents('/path/to/pidfile.pid', $pid);*/