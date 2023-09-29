<?php

namespace aton\tools\Exchange\RabbitMQ\Consumer;

use aton\tools\Exchange\RabbitMQ\Handler\MessageHandlerInterface;
use aton\tools\Exchange\RabbitMQ\Handler\MutualFund;
use aton\tools\Log\FileLog\Log;
use PhpAmqpLib\Message\AMQPMessage;
use SimpleXMLElement;

class MessageHandler {
    public function handleMessage(AMQPMessage $msg) {
        try {
            $cleanXml = preg_replace('/(<\/?|\s)(\w+:)/', '$1', $msg->body);
            $xml = new SimpleXMLElement($cleanXml);
            // Получение имени корневого элемента
            $messageType = $xml->getName();

            Log::log($messageType, 'handler.log');
            //Log::log($msg->body, 'message_body.log'); //временно сохраняем всё тело сообщений для теста

            $hanle = self::createHandle($messageType);
            $hanle->handle($xml);
        } catch (\InvalidArgumentException $e) {
            Log::log("There is no method : " . $messageType, 'rabbit_error.log');
        } catch (\Exception $e) {
            Log::log("Failed to handle XML: " . $e->getMessage() , 'rabbit_error.log');
            Log::log($msg->body , 'rabbit_error.log');
        } finally { //Выполняется в любом случае, даже есть есть ошибка
            $msg->ack(); //помечает сообщение как прочитанное
        }


    }

    /**
     * Фабрика классов обработки xml документов
     * Нужно создать класс с именем метода и реализующего MessageHandlerInterface и он автоматически подключится для обработки xml из шины
     * @param string $messageType
     * @return MessageHandlerInterface
     */
    public static function createHandle(string $messageType): MessageHandlerInterface {

        $className = "aton\\tools\\Exchange\\RabbitMQ\\Handler\\" . $messageType;

        if (!class_exists($className)) {
            throw new \InvalidArgumentException("Class $className does not exist");
        }

        $handler = new $className();

        if (!$handler instanceof MessageHandlerInterface) {
            throw new \InvalidArgumentException("Class $className is not a valid handler");
        }

        return $handler;
    }
}