<?php

namespace App\Controller;

use App\Entity\Mot;
use App\Entity\Langue;
use App\Entity\Suggestion;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Form\AjouterMotType;

class DictionnaireController extends AbstractController
{

    #[Route('/GestionDuJeu', name: 'app_dictionnaire')]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        $em = $doctrine->getManager();
        $listeMots = $doctrine->getRepository(Mot::class)->findALL();
        $listeMotsSuggere = $doctrine->getRepository(Suggestion::class)->findALL();

        $mot = new Mot;
        $form = $this->createform(AjouterMotType::class,$mot);
        $form->handleRequest($request);
        if($request->isMethod('post') && $form->isValid()){
            $em->persist($mot);
            $em->flush();
            $session = $request->getSession();
            $session->getFlashbag()->add('action','Le mot a été ajouté');
            return $this->redirect($this->generateURL('app_dictionnaire'));
        }
        return $this->render('dictionnaire/index.html.twig', [
            'controller_name' => 'DictionnaireController',
            'Title' => 'Gestion du jeu',
            'form' => $form->createView(),
            'listMots' => $listeMots,
            'listeMotsSuggere' =>$listeMotsSuggere
        ]);
    }
}
