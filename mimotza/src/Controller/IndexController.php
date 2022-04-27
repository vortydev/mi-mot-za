<?php

namespace App\Controller;

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
        $entityManager = $doctrine->getManager();

        $historicRepos = $entityManager->getRepository(Historique::class);

        $historique = $historicRepos->findAll();

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'historique' => $historique,
        ]);
    }
}
