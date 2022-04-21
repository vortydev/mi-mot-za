<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event')]
    public function event(Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $eventRepos = $entityManager->getRepository();

        $event;      // $event sera une entité

        if ($request->isMethod('post')) {

            // Ajouter les méthodes pour ajouter un événement

            $eventRepos->add($event);

            return new RedirectResponse('/index');
        }

        return $this->render('event/event.html.twig');
    }
}
