<?php

namespace App\Controller;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    #[Route('/messages', name: 'messages')]
    public function index(
        EntityManagerInterface $em,
        PaginatorInterface $paginator,
        Request $request,
        ManagerRegistry $doctrine
    ): Response {
        $messageRepository = $doctrine->getRepository(Message::class);

        $dql = $messageRepository->getQueryForAll();
        $query = $em->createQuery($dql);

        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('message/index.html.twig', ['pagination' => $pagination]);
    }
}