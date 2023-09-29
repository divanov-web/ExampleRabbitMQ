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
            // get root name
            $messageType = $xml->getName();

            Log::log($messageType, 'handler.log');

            $hanle = self::createHandle($messageType);
            $hanle->handle($xml);
        } catch (\InvalidArgumentException $e) {
            Log::log("There is no method : " . $messageType, 'rabbit_error.log');
        } catch (\Exception $e) {
            Log::log("Failed to handle XML: " . $e->getMessage() , 'rabbit_error.log');
            Log::log($msg->body , 'rabbit_error.log');
        } finally {
            $msg->ack(); //mark message as read
        }


    }

    /**
     * Factory Method for XML document handlers.
     * Create a class named after the method implementing MessageHandlerInterface, and it'll auto-handle XML from the bus.
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