<?php
/**
 * Bus message handler with the "MutualFundFull" header
 */
namespace aton\tools\Exchange\RabbitMQ\Handler;

use aton\tools\Log\FileLog\Log;
use aton\tools\Tables\Fund\CurrencyTable;
use aton\tools\Tables\Fund\FundCostTable;
use aton\tools\Tables\Fund\FundTable;
use Bitrix\Main\Type\DateTime as BDateTime;
use SimpleXMLElement;

class MutualFundFull implements MessageHandlerInterface
{
    public function handle(SimpleXMLElement $xml)
    {
        $guid = (string)$xml->ID;
        foreach ($xml->Data->MutualFundList->MutualFund as $mutualFund) {
            //some logic
        }
    }
}