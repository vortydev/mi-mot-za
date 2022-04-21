<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ControllerDictionnaireController extends AbstractController
{
    #[Route('/controller/dictionnaire', name: 'app_controller_dictionnaire')]
    public function index(): Response
    {
        return $this->render('controller_dictionnaire/index.html.twig', [
            'controller_name' => 'ControllerDictionnaireController',
        ]);
    }
}
