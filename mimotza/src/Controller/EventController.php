<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Utilisateur;
use App\Entity\Evenement;
use App\Entity\Historique;

use App\Repository\UtilisateurRepository;
use App\Repository\EvenementRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event')]
    public function event(Request $request, ManagerRegistry $doctrine): Response
    {
        $response = "";

        if ($request->isMethod('post')) {

            // Récupère le post
            $post = $request->request->all();

            // Instancie l'entity manager
            $entityManager = $doctrine->getManager();

            // Instancie les repos
            $userRepos = $entityManager->getRepository(Utilisateur::class);
            $eventTypeRepos = $entityManager->getRepository(Evenement::class);

            // Instancie l'utilisateur et le type d'événement à partir des id
            $user = $userRepos->findOneBy(['id' => $post['userId']]);
            $eventType = $eventTypeRepos->findOneBy(['id' => $post['eventType']]);

            // Si l'utilisateur est inexistant
            if (!$user) {
                http_response_code(400);
                $response = '{"erreur": "Aucun utilisateur a l\'identifiant ' . $post['userId'] . '"}';
            }
            // Sinon si l'événement n'existe pas
            elseif (!$eventType) {
                http_response_code(400);
                $response = '{"erreur": "Aucun type d\'événement a l\'identifiant ' . $post['eventType'] . '"}';
            }
            else {
                // Créé l'événement à partir du post reçu
                $event = new Historique;

                // Place les infos dans l'événement
                $event->setIdUser($user)
                    ->setIdEvent($eventType)
                    ->setDetail($post['mess'])
                    ->setDateEmission($post['date']);

                // Sauvegarde l'événement
                $entityManager->persist($event);
                $entityManager->flush();

                $response = $post;
            }
            
            // Affiche le message d'erreur
            echo $response;
            exit;
        }

        return $this->render('event/event.html.twig');
    }
}
