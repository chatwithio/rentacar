<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    private MessageRepository $messageRepository;
    private LoggerInterface $logger;
    private EntityManagerInterface $em;

    public function __construct(
        MessageRepository $messageRepository, 
        LoggerInterface $logger,
        EntityManagerInterface $em,
    )
    {
        $this->messageRepository = $messageRepository;
        $this->logger = $logger;
        $this->em = $em;
    }

    #[Route('/messages', name: 'messages', methods: ['GET'])]
    public function index(
        PaginatorInterface $paginator,
        Request $request,
    ): Response {
        $dql = $this->messageRepository->getQueryForAll();
        $query = $this->em->createQuery($dql);

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('message/index.html.twig', ['pagination' => $pagination]);
    }

    #[Route('/message', name: 'message', methods: ['GET', 'POST'])]
    public function new(Request $request)
    {
        $parameters = json_decode($request->getContent(), true)['statuses'][0];
        $this->logger->info($parameters['type']);

        $message = new Message();
        if ($parameters['status'] === 'delivered') {
            $message->setDelivered(true);
        } else if ($parameters['status'] === 'read') {
            $message->setRead(true);
        } else if ($parameters['status'] === 'sent') {
            $message->setSent(true);
        }
        $message->setMessageType($parameters['type']);
        $message->setMessageTo($parameters['recipient_id']);
        $message->setMessageFrom($parameters['id']);
        $message->setMessageContent('Demo');

        $this->messageRepository->add($message, true);
        
        return $this->redirectToRoute('messages', [], Response::HTTP_SEE_OTHER);
    }
}