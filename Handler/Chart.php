<?php
/**
 * Bus message handler with the "Chart" header
 */
namespace aton\tools\Exchange\RabbitMQ\Handler;

use SimpleXMLElement;

class Chart implements MessageHandlerInterface {
    public function handle(SimpleXMLElement $xml) {
        foreach($xml->Data->ChartList as $chart) {
            //some logic
        }
    }
}