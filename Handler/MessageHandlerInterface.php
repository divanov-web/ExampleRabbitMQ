<?php

namespace aton\tools\Exchange\RabbitMQ\Handler;

use \SimpleXMLElement;

interface MessageHandlerInterface {
    public function handle(SimpleXMLElement $xml);
}