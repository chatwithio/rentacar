<?php

// src/MessageHandler/SmsNotificationHandler.php
namespace App\MessageHandler;

use App\Message\WhatsappNotification;
use App\Service\MessageProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Entity\Message;



#[AsMessageHandler]
class WhatsappNotificationHandler
{
    private LoggerInterface $logger;

    private  $processMessage;

    public function __construct(LoggerInterface $logger, MessageProcessor $processMessage)
    {
        $this->logger = $logger;
        $this->processMessage = $processMessage;
    }

    public function __invoke(WhatsappNotification $message)
    {
        $datas = json_decode($message->getContent(), true);
        $this->processMessage->process($datas);
    }
}
