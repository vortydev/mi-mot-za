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
Date: 24/04/2022 Nom: Isabelle Rioux Description: Ajustement de l'affichage d'un joueur avec la base de données
Date: 26/04/2022 Nom: Isabelle Rioux Description: Gestion de la recherche d'un joueur et du bannissement
...
=========================================================
****************************************/
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Role;
use App\Repository\RoleRepository;
use App\Entity\Statut;
use App\Repository\StatutRepository;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;

class UserController extends AbstractController
{
    #[Route('/user', name: 'user')]
    public function index(ManagerRegistry $regis): Response
    {
        $form=$this->createFormBuilder()
        ->setAction($this->generateUrl('result'))
        ->setMethod('POST')
        ->add('username', SearchType::class, ['label'=>'Rechercher un joueur'])
        ->add('envoyer', SubmitType::class, ['label'=>'Envoyer'])
        ->getForm();
        
        $userRepository = $regis->getRepository(Utilisateur::class);
        $users = $userRepository->findAll();
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'list_users' => $users,
            'form_user'=>$form->createView()
        ]);
    }

    #[Route('/user/{id}', name: 'particular_user')]
    public function showUser(ManagerRegistry $regis, $id): Response
    {
        //deal with ban dans fonction si ban set inactif else set ban
        $userRepository = $regis->getRepository(Utilisateur::class);
        $user = $userRepository->findOneBy(['id'=>$id]);

        if (isset($user)){
            return $this->render('user/user.html.twig', [
                'controller_name' => 'UserController',
                'user' => $user
            ]);
        }
        else{
            return $this->render('user/error.html.twig', [
                'controller_name' => 'UserController',
            ]);
        }
        
    }

    #[Route('/resultuser', name: 'result')]
    public function showResearchResult(ManagerRegistry $regis): Response
    {
        $request = Request::createFromGlobals();
        $username = $request->get('form');
        //deal with ban dans fonction si ban set inactif else set ban
        $userRepository = $regis->getRepository(Utilisateur::class);
        $user = $userRepository->findOneBy(['username'=>$username]);

        if (isset($user)){
            return $this->render('user/user.html.twig', [
                'controller_name' => 'UserController',
                'user' => $user
            ]);
        }
        else{
            return $this->render('user/error.html.twig', [
                'controller_name' => 'UserController',
            ]);
        }
    }

    #[Route('/user/{id}/ban', name: 'ban')]
    public function banUser(ManagerRegistry $regis, $id): Response 
    {
        $em = $regis->getManager();
        $userRepository = $regis->getRepository(Utilisateur::class);
        $user = $userRepository->findOneBy(['id'=>$id]);
        $query = $em->createQueryBuilder();

        $query->update('App\Entity\Utilisateur','user');
        $query->set('user.idStatut',':statut');

        if($user->getIdStatut()->getId() == 3){
            $query->setParameter('statut',1);
        }else{
            $query->setParameter('statut',3);
        }

        $query->where('user.id LIKE :id');
        $query->setParameter('id',$id);

        $query->getQuery()->execute();
        if($user->getIdStatut()->getId() == 3){
            return $this->render('user/ban.html.twig', [
                'controller_name' => 'UserController',
                'user' => $user
            ]);
        }else{
            return $this->render('user/unban.html.twig', [
                'controller_name' => 'UserController',
                'user' => $user
            ]);
        }
    }

    #[Route('/inscription', name: 'inscription')]
    public function inscription(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $userManager = $entityManager->getRepository(Utilisateur::class);

        $userList = $userManager->findAll();

        $formInscription = $this->createFormBuilder()
            ->add('prenom', TextType::class, ['label' => 'Prénom'])
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('username', TextType::class, ['label' => 'Nom d\'utilisateur'])
            ->add('mdp', PasswordType::class, ['label' => 'Mot de passe'])
            ->add('submit', SubmitType::class, ['label' => 'S\'inscrire!'])
            ->setAction($this->generateUrl('adduser'))
            ->getForm();

        return $this->render('user/inscription.html.twig', [
            'controller_name' => 'UserController',
            'form' => $formInscription->createView(),
        ]);
    }

    #[Route('/adduser', name: 'adduser')]
    public function addUser(Request $request, ManagerRegistry $doctrine): Response {
        // get post
        $post = $request->request->all();

        // init managers
        $entityManager = $doctrine->getManager();
        $roleManager = $entityManager->getRepository(Role::class);
        $statutManager = $entityManager->getRepository(Statut::class);

        // generate objects
        $roleUsager = $roleManager->findOneBy(['id' => 1]);
        $statutInactif = $statutManager->findOneBy(['id' => 1]);
        $user = new Utilisateur;

        // load user data
        $user->setPrenom($post['form']['prenom'])
            ->setNom($post['form']['nom'])
            ->setEmail($post['form']['email'])
            ->setUsername($post['form']['username'])
            ->setMdp(password_hash($post['form']['mdp'], PASSWORD_DEFAULT))
            ->setAvatar(null)
            ->setIdRole($roleUsager)
            ->setIdStatut($statutInactif)
            ->setDateCreation(date_create_from_format('Y-m-d H:i:s', date('Y-m-d H:i:s')));

        // save user
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->render('user/confirmation.html.twig', [
            'controller_name' => $user->getUsername(),
            'form' => $post['form'],
        ]);
    }
}
