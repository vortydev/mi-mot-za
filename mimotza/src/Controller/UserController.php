<?php

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
                        'temps'=>'4h',
                        'dateCreation'=>'21/04/2022',
                        'statut'=>array('id'=>'1', 'statut'=>'Banni')
                    );

        return $this->render('user/user.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user
        ]);
    }
}
