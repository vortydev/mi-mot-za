<?php

namespace App\Controller;
use App\Entity\Message;
use App\Entity\Utilisateur;
use App\Entity\Thread;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;


class MessageController extends AbstractController
{
    #[Route('/message', name: 'app_message')]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();
        $listeMessages = $doctrine->getRepository(Message::class)->findALL();
        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
            'listeMessages' => $listeMessages
        ]);
    }

    #[Route('/message/utilisateur/{numero}', name: 'joueur_message')]
    public function messageUtilisateur(ManagerRegistry $doctrine, Request $request,$numero): Response
    {
        $em = $doctrine->getManager();
        $listeMessages = $doctrine->getRepository(Message::class)->findBy(array('idUser' => $numero));
        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
            'listeMessages' => $listeMessages
        ]);
    }

    #[Route('/message/ajoutMessage', name: 'ajout_message')]
    public function ajoutMessage(ManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();
        $message = new Message;
        $listeMessages = $doctrine->getRepository(Message::class)->findALL();
        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
            'listeMessages' => $listeMessages
        ]);
    }
}
