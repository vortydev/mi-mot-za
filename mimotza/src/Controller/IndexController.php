<?php

namespace App\Controller;

/****************************************
Fichier : indexController.php
Auteur : Alberto
Fonctionnalité : S'occupe de gèrer les mots en relation avec le jeu
Date : 13 avril 2022
Vérification :
Date Nom Approuvé
=========================================================
Historique de modifications :
13 avril 2022, Alberto, Creation du fichier pour avoir un index dans le la page web

=========================================================
****************************************/

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Historique;
use App\Entity\Evenement;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(ManagerRegistry $doctrine): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }else if ($this->getUser()->getIdRole()->getRole() != "Administrateur") {
            return $this->redirectToRoute('app_logout');
        }

        $entityManager = $doctrine->getManager();

        $historicRepos = $entityManager->getRepository(Historique::class);

        $historique = $historicRepos->findAll();

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'historique' => $historique,
        ]);
    }
}
