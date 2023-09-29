<?php
/**
 * Bus message handler with the "MutualFund" header
 */
namespace aton\tools\Exchange\RabbitMQ\Handler;

use SimpleXMLElement;

class MutualFund implements MessageHandlerInterface {
    public function handle(SimpleXMLElement $xml) {
        foreach($xml->Data->MutualFundList as $mutualFund) {
            //some logic
        }
    }
}