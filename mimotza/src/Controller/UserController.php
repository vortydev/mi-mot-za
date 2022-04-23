<?php
/****************************************
Fichier : UserController.php
Auteur : Isabelle Rioux, Étienne Ménard
Fonctionnalité : Classe UserController pour la gestion des joueurs
Date : 21/04/2022
Vérification :
Date Nom Approuvé
=========================================================
Historique de modifications :
Date: 21/04/2022 Nom: Isabelle Rioux Description: Ajout de la fonction showUser
Date 2 Nom 2 Description 2
...
=========================================================
****************************************/
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Utilisateur;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/user/{id}', name: 'particular_user')]
    public function showUser(ManagerRegistry $regis, $id): Response
    {
        $userRepository = $regis->getRepository(Utilisateur::class);
        //$user = $userRepository->findOneBy(['id'=>$id]);

        $user = array('username'=>'bob',
                        'avatar'=>'none',
                        'parties'=>array(array('id'=>'1','win'=>true,'temps'=>'23:55:10'),array('id'=>'2','win'=>false, 'temps'=>'1:05:55')),
                        'dateCreation'=>'21/04/2022',
                        'statut'=>array('id'=>'1', 'statut'=>'Banni')
                    );

        return $this->render('user/user.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user
        ]);
    }
}
