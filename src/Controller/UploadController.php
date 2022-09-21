<?php

namespace App\Controller;

use App\Form\UploadType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{
    #[Route('/admin/upload', name: 'app_upload')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(UploadType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $xmlFile = $form->get('xmlFile')->getData();

            if ($xmlFile) {
                try {
                    $xmlFile->move(
                        '/home/wardazo/rentacar/xml/',
                        'resentrega.xml'
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $this->addFlash("success", "Archivo subido con exito");
            }
        }
        return $this->render('upload/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}