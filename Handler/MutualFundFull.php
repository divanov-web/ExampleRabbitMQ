<?php
/**
 * Обработчик сообщения шины с заголовком MutualFundFull
 */
namespace aton\tools\Exchange\RabbitMQ\Handler;

use aton\tools\Log\FileLog\Log;
use aton\tools\Tables\Fund\CurrencyTable;
use aton\tools\Tables\Fund\FundCostTable;
use aton\tools\Tables\Fund\FundTable;
use Bitrix\Main\Type\DateTime as BDateTime;
use SimpleXMLElement;

class MutualFundFull implements MessageHandlerInterface {
    public function handle(SimpleXMLElement $xml) {
        $guid = (string)$xml->ID;
        foreach ($xml->Data->MutualFundList->MutualFund as $mutualFund) {
            Log::log(["GUID" => $guid, "DATE" => (string)$mutualFund->NAVCalculationDate, 'NAME' => (string)$mutualFund->Name], 'MutualFundFull.log');
            $xmlId = (int)$mutualFund->Id;
            $name = (string)$mutualFund->Name;
            $currencyRate = (float)$mutualFund->CurrencyRateList->CurrencyRate->Rate; //курс доллара (пока другие валюты не шлют)
            $date = new BDateTime((string)$mutualFund->NAVCalculationDate, "Y-m-d");

            $fundId = self::getFundIdByXmlId($xmlId, $name);

            //Все валюты из базы
            $currencies = [];
            $currenciesById = [];
            $rsData = CurrencyTable::getList();
            while ($row = $rsData->fetch())
            {
                $currencies[$row["XML_ID"]] = $row;
                $currenciesById[$row["ID"]] = $row;
            }

            //цены по валютам в xml
            $arPrice = [];
            foreach ($mutualFund->PriceList->Price as $price) {
                $value = (float)$price->Value;
                $currencyXmlId = (int)$price->Currency['NumCurrencyCode'];
                $currency = $currencies[$currencyXmlId];
                if($currency && $value) {
                    $arPrice[$currency['ID']] = $value;
                }
            }

            //СЧА по валютам в xml
            $arScha = [];
            foreach ($mutualFund->NAVList->NAV as $price) {
                $value = (float)$price->Value;
                $currencyXmlId = (int)$price->Currency['NumCurrencyCode'];
                $currency = $currencies[$currencyXmlId];
                if($currency && $value) {
                    $arScha[$currency['ID']] = $value;
                }
            }

            //На каждую валюту своя строка в БД
            foreach ($arPrice as $currencyId => $price) {
                $scha = $arScha[$currencyId];
                $arChart = [
                    'FUND_ID' => $fundId,
                    'CURRENCY_ID' => $currencyId,
                    'DATE' => $date,
                    'SCHA' => $scha,
                    'PRICE' => $price,
                    'CURRENCY_TOD' => $currenciesById[$currencyId]['CODE'] == 'RUR' ? 1 : $currencyRate
                ];

                //Проверяем, есть ли уже в базе
                $rsData = FundCostTable::getList([
                    'select' => ['ID', 'CODE', 'DATE', 'CURRENCY_ID'],
                    'filter' => [
                        'FUND_ID' => $fundId,
                        'CURRENCY_ID' => $currencyId,
                        'DATE' => $date
                    ],
                ]);
                if($row = $rsData->fetch())
                {
                    $res = FundCostTable::update($row['ID'], $arChart);
                    if (!$res->isSuccess()) {
                        Log::log($res->getErrorMessages(), 'MutualFundFull.log');
                    }
                } else {
                    $res = FundCostTable::add($arChart);
                    if (!$res->isSuccess()) {
                        Log::log($res->getErrorMessages(), 'MutualFundFull.log');
                    }
                }
            }


        }
    }

    /**
     * Возвращает фонд из базы
     * Создаёт новый фонд, если его ещё не было
     * @param int $xmlId
     * @param string $name
     * @return int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getFundIdByXmlId(int $xmlId, string $name):int {

        $fundId = 0;
        //Все существующие фонды в базе charts
        $fundChart = [];
        $rsData = FundTable::getList([
            'select' => ['ID', 'CODE', 'XML_ID'],
        ]);
        while ($row = $rsData->fetch())
        {
            $fundChart[$row["XML_ID"]] = $row;
        }

        if(!empty($fundChart[$xmlId])) {
            $fundId = $fundChart[$xmlId]['ID'];
        } else {
            $res = FundTable::add([
                'ACTIVE' => 'Y',
                "NAME" => $name,
                "XML_ID" => $xmlId,
            ]);
            $fundId = $res->getId();
            if($fundId) {
                Log::log("Fund id=" . $xmlId . " added", 'MutualFundFull.log');
            } else {
                Log::log("error to add fund id=" . $xmlId, 'MutualFundFull.log');
            }
        }

        return $fundId;
    }
}