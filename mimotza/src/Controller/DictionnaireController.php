<?php

namespace App\Controller;

use App\Entity\Mot;
use App\Entity\Langue;
use App\Entity\Suggestion;
use App\Entity\EtatSuggestion;
use App\Entity\Utilisateur;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AjouterMotType;

class DictionnaireController extends AbstractController
{
    //Accueil du gestion de mot qui affiche les mots et les suggestion des mts
    #[Route('/GestionDuJeu', name: 'accueil_gestionDuJeu')]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();
        $listeMots = $doctrine->getRepository(Mot::class)->findALL();
        //Cherhce les mots suggeres qui ont letat en attente(id : 1)
        $listeMotsSuggere = $doctrine->getRepository(Suggestion::class)->findBy(array('idEtatSuggestion' => 1));
        $mot = new Mot;
        $form = $this->createform(AjouterMotType::class, $mot);
        $form->handleRequest($request);
        if($request->isMethod('post') && $form->isValid()){
            $em->persist($mot);
            $em->flush();
            $session = $request->getSession();
            $session->getFlashbag()->add('action',"Le mot ".$mot->getMot()." a été ajouté");
            return $this->redirect($this->generateURL('accueil_gestionDuJeu'));
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

    //Gestion automatique d'un ajout d'un mot
    #[Route('/GestionDuJeu/acceptSuggestion', name: 'acceptSuggestion')]
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
            // actually executes the queries (i.e. the INSERT query)
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
            $mot->setMot($suggestion->getMotSuggere());
            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $em->persist($mot);
            // actually executes the queries (i.e. the INSERT query)
            $em->flush();
            $session->getFlashBag()->add('delete', "le mot suggeré : ".$suggestion->getMotSuggere()." a été accepté");
        }
        return $this->redirect($this->generateURL('accueil_gestionDuJeu'));
    }


    //Redirecction vers les statistique d'un mot
    #[Route('/GestionDuJeu/{idMot}', name: 'mot_stat')]
    public function statistiquesMot(ManagerRegistry $doctrine, Request $request, $idMot): Response
    {
        $suggestion = new Suggestion;
        $etat = new EtatSuggestion;
        $em=$doctrine->getManager();
        $motrepo = $em->getRepository(Mot::class);
        $mot = $motrepo->find($idMot);

        return $this->render('dictionnaire/statistiqueMot.html.twig', [
            'mot' => $mot->getMot(),
            'idMot' => $idMot,
            'nbFoisJoue' => 1000
        ]);
        
    }

    //Gere une requete api provenant de l'application mobile et ajout une suggestion dans la bd
    #[Route('/ajoutSuggestion', name: 'ajoutSuggestion')]
    public function ajoutSuggestion(ManagerRegistry $doctrine, Request $request )
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
            $em->persist($suggestion);
            $em->flush();
        }
    }


}
