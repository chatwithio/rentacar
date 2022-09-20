<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// ...
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginController extends AbstractController
{
    private $em;
    
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    #[Route('/login', name: 'app_login')]
    public function index(
        Request $request, 
        AuthenticationUtils $authenticationUtils,
        ManagerRegistry $doctrine,
    ): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($request->isMethod('POST')) {
            $email = $request->request->get('_username');
            $user = $doctrine->getRepository(User::class)->findBy(['email' => $email]);

            // TODO:
            // if (!$user) {
            //     return $this->render('login/index.html.twig', [
            //         'controller_name' => 'LoginController',
            //         'last_username' => $lastUsername,
            //         'error' => 'User not found.',
            //     ]);
            // }
            
            // creates a user object and initializes some data for this example
            $user = new User();
            $user->setEmail($request->request->get('_username'));
            $user->setPassword($request->request->get('_password'));
                
            $this->em->persist($user);
            $this->em->flush();

            return $this->redirectToRoute('app_message');
        }

        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }


    #[Route('/', name: 'app_home')]
    public function home(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted('ROLE_USER')){
            return $this->redirectToRoute('app_upload');
        }
        return $this->redirectToRoute('app_login');

    }
}