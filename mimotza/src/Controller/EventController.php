<?php
/****************************************
Fichier : EventController.php
Auteurs : François-Nicolas Gitzhofer
Fonctionnalité : Classe EventController qui permet la gestion d'événements
Date : 21/04/2022
Vérification :
Date Nom Approuvé
=========================================================
Historique de modifications (Approximatif):
Date: 21/04/2022 Nom: François-Nicolas Gitzhofer Description: Ajout de la méthode event
Date: 22/04/2022 Nom: François-Nicolas Gitzhofer Description: Amélioration de la méthode event
Date: 24/04/2022 Nom: François-Nicolas Gitzhofer Description: Ajout de la méthode redirect
Date: 25/04/2022 Nom: François-Nicolas Gitzhofer Description: Amélioration de la gestion d'event de connexion
Date: 26/04/2022 Nom: François-Nicolas Gitzhofer Description: Changement de la gestion d'event de connexion avec un switch case
Date: 27/04/2022 Nom: François-Nicolas Gitzhofer Description: Ajout de la gestion d'event de déconnexion + finalisation EventController
...
=========================================================
****************************************/
namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Utilisateur;
use App\Entity\Evenement;
use App\Entity\Historique;
use App\Entity\Statut;

use App\Repository\UtilisateurRepository;
use App\Repository\EvenementRepository;
use App\Repository\StatutRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Validator\Constraints\DateTime;

class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event')]
    public function event(Request $request, ManagerRegistry $doctrine): Response
    {
        $code = 200;
        $error = null;

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

            $response = json_encode($post);

            // Si l'utilisateur est inexistant
            if (!$user) {
                http_response_code(400);
                $code = 400;
                $error = "Aucun utilisateur possède l\'identifiant" . $post['userId'];
            }
            // Sinon si l'événement n'existe pas
            elseif (!$eventType) {
                http_response_code(400);
                $code = 400;
                $error = "Aucun type d\'événement possède l\'identifiant " . $post['eventType'];
            }
            else {
                // Créé l'événement à partir du post reçu
                $event = new Historique;

                // Sauvegarde la date d'aujourd'hui
                $date = date('Y-m-d H:i:s');
                $mess = $post['mess'];

                //echo 'Message: ' . $mess . "\nDate: $date\n";
                $mess = preg_replace("/\[date\]/", $date, $mess);
                //echo 'Message: ' . $mess . "\n";

                // Place les infos dans l'événement
                $event->setIdUser($user)
                    ->setIdEvent($eventType)
                    ->setDetail($mess)
                    ->setDateEmission(date_create_from_format('Y-m-d H:i:s', $date));

                // Sauvegarde l'événement
                $entityManager->persist($event);
                $entityManager->flush();
            }
            
            // Affiche le message d'erreur
            if ($error) {
                $response .= "\nCode: " . $code . "\nErreur: " . $error;
            }
            echo $response;
            exit;
        }

        return $this->render('event/event.html.twig');
    }

    // Le premier paramètre est l'id de l'utilisateur
    // Le deuxième paramètre est l'id du type d'événement
    // Le troisième paramètre est où la page doit
    #[Route('/redirect/{userId}/{eventType}/{whereTo}', name: 'app_eventRedirect')]
    public function eventRedirect(ManagerRegistry $doctrine, int $userId, int $eventType, string $whereTo): Response {

        if (isset($userId)) {

            $entityManager = $doctrine->getManager();
            $userRepos = $entityManager->getRepository(Utilisateur::class);

            $user = $userRepos->findOneBy(['id' => $userId]); 

            $whereTo = preg_replace('/\|/', '/', $whereTo);

            switch ($eventType) {

                case 2:
                    if (isset($user) && $user->getIdStatut()->getId() != 2) {
                        
                        $statutRepos = $entityManager->getRepository(Statut::class);                        
                        $statut = $statutRepos->findOneBy(['id' => 2]);

                        $user->setIdStatut($statut);

                        $entityManager->flush();

                        return $this->render('event/redirect.html.twig', [
                            'eventType' => $eventType,
                            'whereTo' => $whereTo
                        ]);
                    }
                    break;
                case 3:
                    if (isset($user) && $user->getIdStatut()->getId() == 2) {

                        $statutRepos = $entityManager->getRepository(Statut::class);
                        $statut = $statutRepos->findOneBy(['id' => 3]);

                        $user->setIdStatut($statut);

                        $entityManager->flush();

                        return $this->render('event/redirect.html.twig', [
                            'eventType' => $eventType,
                            'whereTo' => $whereTo
                        ]);
                    }
                    break;
            }
        }

        return $this->redirectToRoute('app_index');  
    }
}
