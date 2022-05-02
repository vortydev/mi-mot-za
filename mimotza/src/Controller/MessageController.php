<?php

namespace App\Controller;

/****************************************
Fichier : MessageController.php
Auteur : Alberto
Fonctionnalité : S'occupe de gèrer les mots en relation avec le jeu
Date : 13 avril 2022
Vérification :
Date Nom Approuvé
=========================================================
Historique de modifications :
13 avril 2022, Alberto, Gestion d'affichage des messages
17 avril 2022, Alberto, Gestion d'ajout et suppresion de suggestions d'un message ou thread
27 avril 2022, Alberto, ajout du formulaire for filtrer les message ou threads par utilisateur
2 mai 2022, François-Nicolas, ajout des gestions de requêtes pour les threads
...
=========================================================
****************************************/

use App\Entity\Message;
use App\Entity\Utilisateur;
use App\Entity\Thread;

use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class MessageController extends AbstractController
{

    private function getReponses($message): ?array {

        if (!count($message->getMessages())) {
            return null;
        }

        $reponses = array();
        foreach ($message->getMessages() as $m) {

            $reponses[$m->getId()] = array();
            $reponses[$m->getId()]['Auteur'] = $m->getIdUser()->getUsername();
            $reponses[$m->getId()]['Message'] = $m->getContenu();
            $reponses[$m->getId()]['Reponses'] = $this->getReponses($m);
        }

        return $reponses;
    }

    #[Route('/message', name: 'app_message')]
    /**
    *  @Security("is_granted('ROLE_ADMIN')")
    */
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {

        $form=$this->createFormBuilder()
        ->setAction($this->generateUrl('joueur_message'))
        ->setMethod('POST')
        ->add('username', SearchType::class, ['label'=>' '])
        ->add('envoyer', SubmitType::class, ['label'=>'Filtrer par joueur'])
        ->getForm();

        $listeMessages = $doctrine->getRepository(Message::class)->findALL();
        $listeThreads = $doctrine->getRepository(Thread::class)->findALL();
        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
            'listeMessages' => $listeMessages,
            'listeThread' => $listeThreads,
            'form_user'=> $form->createView(),
            'userName' => ''
        ]);
    }

    #[Route('/message/utilisateur', name: 'joueur_message')]
    public function messageUtilisateur(ManagerRegistry $doctrine, Request $request): Response
    {

        $em = $doctrine->getManager();
        $post = $request->request->all();
        $username = $request->get('form');
        $user = $doctrine->getRepository(Utilisateur::class)->findBy(array('username' =>$username));

        if(!$user){
            $session = $request->getSession();
            $session->getFlashBag()->add('resultat', "l'utilisateur : ".$username['username']." n'existe pas");
            return $this->redirect($this->generateURL('app_message'));
        }
        
        $listeMessages = $doctrine->getRepository(Message::class)->findBy(array('idUser' =>$user[0]->getId()));
        $listeThread = $doctrine->getRepository(Thread::class)->findBy(array('idUser' => $user[0]->getId()));

        $form=$this->createFormBuilder()
        ->setAction($this->generateUrl('joueur_message'))
        ->setMethod('POST')
        ->add('username', SearchType::class, ['label'=>' '])
        ->add('envoyer', SubmitType::class, ['label'=>'Filtrer par joueur'])
        ->getForm();

        return $this->render('message/index.html.twig', [
            'controller_name' => 'MessageController',
            'listeMessages' => $listeMessages,
            'listeThread' =>$listeThread,
            'form_user'=>$form->createView(),
            'userName' => $user[0]->getUsername()
        ]);
    }

    
    //Gere une requete api provenant de l'application mobile et un thread ou un message dans la bd
    // la fonction s'adapte si c'est un message qui repond a un autre message ou un thread avec un messagea l'interiur
    #[Route('/ajoutMedia', name: 'ajoutMedia')]
    public function ajoutMedia(ManagerRegistry $doctrine, Request $request ) : Response
    {

        if($request->isMethod('post')){
            $em=$doctrine->getManager();
            $post = $request->request->all();
            $message = new Message;

            $utilisateur =$doctrine->getRepository(Utilisateur::class)->find($post['idUser']);

            $message->setIdUser($utilisateur);
            $message->setDateEmission(new \DateTime('now'));
            $message->setContenu($post['contenu']);
            if(isset($post['idMessageParent'])) {
                $messageParent = $doctrine->getRepository(Message::class)->find($post['idMessageParent']);
                $message->setIdParent($messageParent);
            }
           
            try {
                $em->persist($message);
                $em->flush();
            } catch(Exception $e) {
                $response = new Response();
                $response->setStatusCode(400);
                return $response;
            }

            if(isset($post['thread']) && !(isset($post['idMessageParent']))){
               
                
                try {
                    $thread = new Thread;
                    $thread->setIdUser($utilisateur);
                    $thread->setIdMessage($message);
                    $thread->setDateEmission(new \DateTime('now'));
                    $thread->setTitre($post['titre']);
                    $em->persist($thread);
                    $em->flush();
                } catch(Exception $e) {
                    $response = new Response();
                    $response->setStatusCode(400);
                    return $response;
                }
                
            }
            $response = new Response();
            $response->setStatusCode(200);
            return $response;
        }

    }


    //Gere une requete api provenant de l'application mobile et un thread ou un message dans la bd
    // la fonction s'adapte si c'est un message qui repond a un autre message ou un thread avec un messagea l'interiur
    #[Route('/supprimerMedia', name: 'supprimerMedia')]
    public function supprimerMedia(ManagerRegistry $doctrine, Request $request )
    {
        
        if($request->isMethod('post')){
            
            
            try {
                $em=$doctrine->getManager();
                $post = $request->request->all();
                $message = new Message;
                if ($post['supprimer'] == 'Thread'){
                    $thread = $doctrine->getRepository(Thread::class)->find($post['idThread']);
                    $thread->setTitre('Ce contenu a été par l\'Utilisateur');
                    $message =$thread->getIdMessage();
                    $message->setContenu('Ce contenu a été par l\'Utilisateur');
                }
                if ($post['supprimer'] == 'Message'){
                    $message =$doctrine->getRepository(Message::class)->find($post['idMessage']);
                    $message->setContenu('Ce contenu a été par l\'Utilisateur');
                }
                    $em->persist($message);
                    $em->flush();
            } catch(Exception $e) {
                $response = new Response();
                $response->setStatusCode(400);
                return $response;
            }

            $response = new Response();
            $response->setStatusCode(200);
            return $response;
        }

    }

    // Gère une requête API venant de l'application mobile
    // Demandant tous les threads à afficher dans le forum
    #[Route('/getAllMedia', name:'getAllMedia')]
    public function getAllMedia(ManagerRegistry $doctrine, Request $request) {

        if ($request->isMethod('post')) {

            try {
                $em=$doctrine->getManager();
                
                $threadRepos = $em->getRepository(Thread::class);

                $threads = $threadRepos->findAll();

                $json = array();

                foreach ($threads as $thread) {

                    $message = $thread->getIdMessage();
                    $json['Thread' . $thread->getId()] = array();
                    $json['Thread' . $thread->getId()]['IDThread'] = $thread->getId();
                    $json['Thread' . $thread->getId()]['Titre'] = $thread->getTitre();
                    $json['Thread' . $thread->getId()]['Auteur'] = $thread->getIdUser()->getUsername();
                    $json['Thread' . $thread->getId()]['IDMessage'] = $message->getId();
                    $json['Thread' . $thread->getId()]['Message'] = $message->getContenu();
                    $json['Thread' . $thread->getId()]['NbReponses'] = count($message->getMessages());
                }

                $jsonText = json_encode($json);
            } catch(Exception $e) {
                $response = new Response();
                $response->setStatusCode(400);
                return $response;
            }
            finally {

                $response = new Response();
                $response->setContent($jsonText);
                $response->headers->set('Content-Type', 'application/json');
                $response->setStatusCode(200);
                return $response;
            }
        }
    }

    // Gère une requête d'API venant de l'app mobile
    // Redonne le thread qui possède l'id envoyé en paramètre
    #[Route('/getMedia/{threadId}', name:'getMedia')]
    public function getMedia(ManagerRegistry $doctrine, Request $request, int $threadId) {

        if ($request->isMethod('post')) {

            //$jsonText = "";
            try {
                $em=$doctrine->getManager();
                
                $threadRepos = $em->getRepository(Thread::class);

                $thread = $threadRepos->findOneBy(['id' => $threadId]);

                $json = array();

                $message = $thread->getIdMessage();
                
                $json['IDThread'] = $thread->getId();
                $json['Titre'] = $thread->getTitre();
                $json['Auteur'] = $thread->getIdUser()->getUsername();
                $json['IDMessage'] = $message->getId();
                $json['Message'] = $message->getContenu();
                $json['Reponses'] = $this->getReponses($message);

                $jsonText = json_encode($json);
            } catch(Exception $e) {
                $response = new Response();
                $response->setStatusCode(400);
                return $response;
            }
            finally {

                $response = new Response();
                $response->setContent($jsonText);
                $response->headers->set('Content-Type', 'application/json');
                $response->setStatusCode(200);
                return $response;
            }
        }
    }
}
