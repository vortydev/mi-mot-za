<?php
/****************************************
Fichier : UserController.php
Auteurs : Isabelle Rioux, Étienne Ménard
Fonctionnalité : Classe UserController pour la gestion des joueurs
Date : 21/04/2022
Vérification :
Date Nom Approuvé
=========================================================
Historique de modifications :
Date: 21/04/2022 Nom: Isabelle Rioux Description: Ajout de la fonction showUser
Date: 21/04/2022 Nom: Étienne Ménard Description: Ajout de la fonction addUser
Date: 24/04/2022 Nom: Isabelle Rioux Description: Ajustement de l'affichage d'un joueur avec la base de données
Date: 26/04/2022 Nom: Isabelle Rioux Description: Gestion de la recherche d'un joueur et du bannissement
Date: 26/04/2022 Nom: Étienne Ménard Description: Insertion d'utilisateurs dans la BD à partir d'un tableau JSON
...
=========================================================
****************************************/
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    #[Route('/inscription', name: 'inscription')]
    public function inscription(ManagerRegistry $doctrine): Response {
        $formInscription = $this->createFormBuilder()
            ->add('prenom', TextType::class, ['label' => 'Prénom'])
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('username', TextType::class, ['label' => 'Nom d\'utilisateur'])
            ->add('mdp', PasswordType::class, ['label' => 'Mot de passe'])
            ->add('submit', SubmitType::class, ['label' => 'S\'inscrire!'])
            ->add('sender', HiddenType::class, [

                    'attr' => [
                        'value' => 'formWebsite'
                    ]
                ]
            )
            ->setAction($this->generateUrl('adduser'))
            ->getForm();

            return $this->render('user/inscription.html.twig', [
                'form' => $formInscription->createView()
            ]);
    }
    #[Route('/user/{id}/ban', name: 'ban')]
    public function banUser(ManagerRegistry $regis, $id): Response 
    {

        $em = $regis->getManager();
        $userRepository = $regis->getRepository(Utilisateur::class);
        $user = $userRepository->findOneBy(['id'=>$id]);
        if (isset($user)){
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
                return $this->render('user/unban.html.twig', [
                    'controller_name' => 'UserController',
                    'user' => $user
                ]);
            }else if($user->getIdStatut()->getId() == 1 ||$user->getIdStatut()->getId() == 2){
                return $this->render('user/ban.html.twig', [
                    'controller_name' => 'UserController',
                    'user' => $user
                ]);
            }
        }else{
            return $this->render('user/error.html.twig', [
                'controller_name' => 'UserController',
            ]);
        }
    }

    #[Route('/adduser', name: 'adduser')]
    public function addUser(Request $request, ManagerRegistry $doctrine): Response {
        // get post TEMP
        // $post = $request->request->all();
        // $encode = json_encode(array($post['form'], $post['form']));

        $post = $request->request->all();

        // init managers
        $entityManager = $doctrine->getManager();
        $roleManager = $entityManager->getRepository(Role::class);
        $statutManager = $entityManager->getRepository(Statut::class);
        $userManager = $entityManager->getRepository(Utilisateur::class);

        // generate objects
        $roleUsager = null;
        if (isset($post['form']['sender']) && $post['form']['sender'] == 'formWebsite') {
            $roleUsager = $roleManager->findOneBy(['role' => 'Administrateur']);
        }
        else {
            $roleUsager = $roleManager->findOneBy(['role' => 'Usager']);
        }
        //$roleUsager = $roleManager->findOneBy(['id' => 1]);
        $statutInactif = $statutManager->findOneBy(['id' => 1]);

        // TEMP
        $liste = array(
            array(
                'prenom' => 'Étienne',
                'nom' => 'Ménard',
                'email' => 'etienne.dmenard@gmail.com',
                'username' => 'vorty',
                'mdp' => 'abc123',
                'role' => 2,
                'statut' => 2,
            ),
            array(
                'prenom' => 'Isabelle',
                'nom' => 'Rioux',
                'email' => 'isabelle.rioux@gmail.com',
                'username' => 'isa',
                'mdp' => 'abc123'
            ),
        );

        $json = json_encode($liste);

        // TODO get json array
        // $data = json_decode($request->getContent(), true);
        $data = json_decode($json, true);

        $users = array();

        if (isset($post['form']['sender']) && $post['form']['sender'] == 'formWebsite') {
            $user = new Utilisateur();
            $form = $post['form'];
            $user->setPrenom($form['prenom'])
                ->setNom($form['nom'])
                ->setEmail($form['email'])
                ->setUsername($form['username'])
                ->setMdp($form['mdp'])
                ->setAvatar(null)
                ->setIdRole($roleUsager)
                ->setIdStatut($statutInactif)
                ->setDateCreation(date_create_from_format('Y-m-d H:i:s', date('Y-m-d H:i:s')));

            $entityManager->persist($user);
        }
        else {
            // loop through array and load create each user
            foreach ($data as $u) {
                $emailCheck = $userManager->findOneBy(['email' => $u['email']]);
                $usernameCheck = $userManager->findOneBy(['username' => $u['username']]);

                if ($emailCheck == null && $usernameCheck == null) {
                    $user = new Utilisateur();

                    // load user data
                    $user->setPrenom($u['prenom'])
                    ->setNom($u['nom'])
                    ->setEmail($u['email'])
                    ->setUsername($u['username'])
                    ->setMdp(password_hash($u['mdp'], PASSWORD_DEFAULT))
                    ->setAvatar(null)
                    ->setDateCreation(date_create_from_format('Y-m-d H:i:s', date('Y-m-d H:i:s')));

                    // set role
                    if (!empty($u['role']) && $roleManager->findOneBy(['id' => $u['role']]) != null) {
                        $user->setIdRole($roleManager->findOneBy(['id' => $u['role']]));
                    }
                    else {
                        $user->setIdRole($roleUsager);
                    }
                    
                    // set statut
                    if (!empty($u['statut']) && $statutManager->findOneBy(['id' => $u['statut']]) != null) {
                        $user->setIdStatut($statutManager->findOneBy(['id' => $u['statut']]));
                    }
                    else {
                        $user->setIdStatut($statutInactif);
                    }

                    if (!empty($u['avatar'])) {
                        $user->setAvatar($u['avatar']);
                    }

                    array_push($users, $user);

                    // save user
                    $entityManager->persist($user);
                }
            }
        }

        // push to bd
        $entityManager->flush();        

        if (isset($post['form']['sender']) && $post['form']['sender'] == 'formWebsite') {

            return $this->render('user/confirmation.html.twig', [
                'controller_name' => 'poggers',
                'form' => $post['form'],
            ]);
        }
        else {
            return $this->render('user/confirmation.html.twig', [
                'controller_name' => 'poggers',
                'users' => $users
            ]);
        }
    }
}
