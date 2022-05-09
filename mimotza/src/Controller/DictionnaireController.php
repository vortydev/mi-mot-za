<?php
namespace App\Controller;


/****************************************
Fichier : DictionnaireController.php
Auteur : Alberto
Fonctionnalité : S'occupe de gèrer les mots en relation avec le jeu
Date : 13 avril 2022
Vérification :
Date Nom Approuvé
=========================================================
Historique de modifications :
13 avril 2022, Alberto, Gestion d'affichage des mots
17 avril 2022, Alberto, Gestion d'ajout et suppresion de suggestions de mot
27 avril 2022, Alberto, Gestion des statistiques des mots

...
=========================================================
****************************************/

use App\Entity\Mot;
use App\Entity\Langue;
use App\Entity\Suggestion;
use App\Entity\EtatSuggestion;
use App\Entity\Utilisateur;
use App\Entity\Message;
use App\Entity\Thread;
use App\Entity\Partie;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AjouterMotType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class DictionnaireController extends AbstractController
{
    //Accueil du gestion de mot qui affiche les mots et les suggestion des mts
    #[Route('/GestionDuJeu', name: 'accueil_gestionDuJeu')]
    /**
    *  @Security("is_granted('ROLE_ADMIN')")
    */
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {

        $em = $doctrine->getManager();
        $listeMots = $doctrine->getRepository(Mot::class)->findALL();
        //Cherhce les mots suggeres qui ont letat en attente(id : 1)
        $listeMotsSuggere = $doctrine->getRepository(Suggestion::class)->findBy(array('idEtatSuggestion' => 1));
        $mot = new Mot;
        $form = $this->createform(AjouterMotType::class, $mot);
        $session = $request->getSession();
        $form->handleRequest($request);
       
        if($request->isMethod('post') && $form->isValid()){
            $duplicate = $doctrine->getRepository(Mot::class)->findBy(array('mot' => $mot->getMot()));
            
           
            if(count($duplicate) > 0){
                $session->getFlashBag()->add('action', "Le mot : ".$mot->getMot()."est deja sur la bd");

                return $this->redirect($this->generateURL('accueil_gestionDuJeu'));
            }else {
                $em->persist($mot);
                $em->flush();
                $session = $request->getSession();
                $session->getFlashBag()->add('action', "Le mot : ".$mot->getMot()."a été ajouté");
              

                return $this->redirect($this->generateURL('accueil_gestionDuJeu'));
            }
            
        }
        return $this->render('dictionnaire/index.html.twig', [
            'controller_name' => 'DictionnaireController',
            'Title' => 'Gestion du jeu',
            'form' => $form->createView(),
            'listMots' => $listeMots,
            'listeMotsSuggere' =>$listeMotsSuggere
        ]);
    }

    //Gestion automatique d'un refus d'une suggestion des mots
    #[Route('/GestionDuJeu/refuseSuggestion', name: 'refuseSuggestion')]
    /**
    *  @Security("is_granted('ROLE_ADMIN')")
    */
    public function refuseSuggestion(ManagerRegistry $doctrine, Request $request): Response
    {

        $id = $_GET['id'];
        $suggestion = new Suggestion;
        $etat = new EtatSuggestion;
        $em=$doctrine->getManager();
        $suggestionrepo = $em->getRepository(Suggestion::class);
        $suggestion = $suggestionrepo->find($id);
        $etatrepo = $em->getRepository(EtatSuggestion::class);
        $etat = $etatrepo->findBy(array('etat' => 'Refusé'));
        $suggestion->setIdEtatSuggestion($etat[0]);
        $session = $request->getSession();
        $session->getFlashBag()->add('delete', "le mot suggèré : ".$suggestion->getMotSuggere()." a été réfusé");
        //$em->remove($suggestion);
        $em->flush();
        return $this->redirect($this->generateURL('accueil_gestionDuJeu'));
    }

    //Gestion automatique d'un ajout d'un mot avec un requete API
    #[Route('/GestionDuJeu/acceptSuggestion', name: 'acceptSuggestion')]
    /**
    *  @Security("is_granted('ROLE_ADMIN')")
    */
    public function acceptSuggestion(ManagerRegistry $doctrine, Request $request): Response
    {

        $id = $_GET['id'];
        $em=$doctrine->getManager();
        $session = $request->getSession();
        $etat = new EtatSuggestion;
        $suggestion = new Suggestion;

        $etatrepo = $em->getRepository(EtatSuggestion::class);
        $suggestionrepo= $em->getRepository(Suggestion::class);
        $suggestion = $suggestionrepo->find($id);
        $motrepo= $em->getRepository(Mot::class);
        
        //Verification si le mot existe deja sur la bd
        if($motrepo->findBy(array('mot' => $suggestion->getMotSuggere()))){
            $session->getFlashBag()->add('delete', "le mot suggeré : ".$suggestion->getMotSuggere()." existe sur la bd : Refusé");
            $etat = $etatrepo->findBy(array('etat' => 'Refusé'));
            $suggestion->setIdEtatSuggestion($etat[0]);
            $em->persist($suggestion);
            $em->flush();
        }else{
            $mot = new Mot;
            $langue = new Langue;
            $languerepo = $em->getRepository(Langue::class);
            $langue = $languerepo->find(1);
            $etat = $etatrepo->findBy(array('etat' => 'Accepté'));
            $suggestion->setIdEtatSuggestion($etat[0]);
            $mot->setIdLangue($langue);
            $mot->setDateAjout(new \DateTime('now'));
            $mot->setMot(strtoupper($suggestion->getMotSuggere()));
            $em->persist($mot);
            $em->flush();
            $session->getFlashBag()->add('delete', "le mot suggeré : ".$suggestion->getMotSuggere()." a été accepté");
        }
        return $this->redirect($this->generateURL('accueil_gestionDuJeu'));
    }


    //Redirecction vers les statistique d'un mot 
    #[Route('/GestionDuJeu/{idMot}', name: 'mot_stat')]
    /**
    *  @Security("is_granted('ROLE_ADMIN')")
    */
    public function statistiquesMot(ManagerRegistry $doctrine, Request $request, $idMot): Response
    {

        $suggestion = new Suggestion;
        $etat = new EtatSuggestion;
        $em=$doctrine->getManager();    
        $motrepo = $em->getRepository(Mot::class);
        $mot = $motrepo->find($idMot);

        $parties = $em->getRepository(Partie::class)->findAll();
        $nbParties = 1;
        $tempsMoyen = new \DateTime('0000-01-01 0:0:0');
        $tempsMoyen->format('H:i:s');
        $tentativesMoyen = 0;
        $partiesGagnesMot = 0;
        for($i = 0; $i < count($parties); $i++){
            if($partie->getMot() == $mot){
                $nbParties ++;
                if($partie->getWin()){
                    $partiesGagnesMot++;
                    $tempsMoyen->add($partie->getTemps()->format('H:i:s'));
                    $tentativesMoyen += $partie[$i]->getScore();
                }
            }

        }

        $tempsMoyenint = $tempsMoyen->getTimestamp();
        $tempsMoyenint = $tempsMoyenint/$nbParties;
        $tentativesMoyen = $tentativesMoyen / $nbParties;
        $tempsMoyen = date('H:i:s', $tempsMoyenint);


        return $this->render('dictionnaire/mot.html.twig', [
            'mot' => $mot->getMot(),
            'nbFoisJoue' => $nbParties - 1,
            'tempMoyen' => $tempsMoyen,
            'tentativesMoyen' => $tentativesMoyen,
            'partiesGagnesMot' => $partiesGagnesMot,
            'idMot' => $mot->getId()
        ]);
        
    }

    //Gere une requete api provenant de l'application mobile et ajoute une suggestion dans la bd
    #[Route('/ajoutSuggestion', name: 'ajoutSuggestion')]
    public function ajoutSuggestion(ManagerRegistry $doctrine, Request $request ) : Response
    {
        if($request->isMethod('post')){
            $post = $request->request->all();
            $suggestion = new Suggestion;
            $em=$doctrine->getManager();
            
            $etat = $doctrine->getRepository(EtatSuggestion::class)->findBy(array('etat' => 'En attente'));
            $langue = $doctrine->getRepository(Langue::class)->findBy(array('langue' => $post['langue']));
            $user =  $doctrine->getRepository(Utilisateur::class)->find($post['idUser']);
            
            $suggestion->setIdUser($user);
            $suggestion->setMotSuggere($post['mot']);
            $suggestion->setIdEtatSuggestion($etat[0]);
            $suggestion->setDateEmission(new \DateTime('now'));
            $suggestion->setIdLangue($langue[0]);
            
            try {
                $em->persist($suggestion);
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
        $response = new Response();
        $response->setStatusCode(400);
        return $response;
    }

    
    #[Route('/supprimerMot', name: 'supprimerMot')]
    public function supprimerMot(ManagerRegistry $doctrine, Request $request ) : Response {
        $session = $request->getSession();
        $idMot = $_GET['idMot'];
        $mot=$doctrine->getRepository(Mot::class)->find($idMot);
        $em=$doctrine->getManager();
        $session->getFlashBag()->add('action', "le mot : ".$mot->getMot()." a été accepté");

        $em->remove($mot);
        $em->flush();

        return $this->redirect($this->generateURL('accueil_gestionDuJeu'));
    
    }
    
}