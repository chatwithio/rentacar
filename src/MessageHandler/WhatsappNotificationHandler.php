<?php

// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Message\WhatsappNotification;
use App\Service\MessageProcessor;
use JetBrains\PhpStorm\NoReturn;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class WhatsappNotificationHandler
{
    private LoggerInterface $logger;
    private MessageProcessor $processMessage;
    static array $ChatBotMessages = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 'a', 'b', 'c'];

    public function __construct(LoggerInterface $logger, MessageProcessor $processMessage)
    {
        $this->logger = $logger;
        $this->processMessage = $processMessage;
    }

    #[NoReturn]
    public function __invoke(WhatsappNotification $message)
    {
        $data = json_decode($message->getContent(), true);

        $chatBotIndex = str_split($data['messages'][0]['text']['body']);

        if (sizeof($chatBotIndex) === 2 && in_array($chatBotIndex[0], self::$ChatBotMessages) &&
            in_array($chatBotIndex[1], self::$ChatBotMessages)) {
            $this->processMessage->menuBasedChatBot($data['contacts'][0]['wa_id'], $chatBotIndex[0], $chatBotIndex[1]);
        } else if (in_array($chatBotIndex[0], self::$ChatBotMessages)) {
            $this->processMessage->menuBasedChatBot($data['contacts'][0]['wa_id'], $chatBotIndex[0]);
        } else {
            $this->processMessage->process($data);
        }

        $this->processMessage->updateMessageStatus($data);
    }
}
