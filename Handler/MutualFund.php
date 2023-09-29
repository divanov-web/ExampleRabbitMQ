<?php
/**
 * Обработчик сообщения шины с заголовком MutualFund
 */
namespace aton\tools\Exchange\RabbitMQ\Handler;

use SimpleXMLElement;

class MutualFund implements MessageHandlerInterface {
    public function handle(SimpleXMLElement $xml) {
        foreach($xml->Data->MutualFundList as $mutualFund) {

        }
    }
}