<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_USER')]
class MessageController extends AbstractController
{
    private MessageRepository $messageRepository;
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
}