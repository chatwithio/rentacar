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

    public function __construct(LoggerInterface $logger, MessageProcessor $processMessage)
    {
        $this->logger = $logger;
        $this->processMessage = $processMessage;
    }

    #[NoReturn]
    public function __invoke(WhatsappNotification $message)
    {
        $data = json_decode($message->getContent(), true);
        $this->processMessage->process($data);
    }
}
