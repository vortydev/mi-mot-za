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
Date: 27/04/2022 Nom: Isabelle Rioux Description: Simplification de l'affichage de ban/unban et bandenied et empecher un admin d'etre banni
Date: 28/04/2022 Nom: Isabelle Rioux Description: Pagination de la liste de joueurs
Date: 28/04/2022 Nom: François-Nicolas Gitzhofer Description: Ajout de l'inscription d'utilisateur via fichier JSON
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
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
use App\Entity\Partie;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Doctrine\ORM\Tools\Pagination\Paginator;

class UserController extends AbstractController
{
    #[Route('/users/{page}', name: 'user')]
    /**
    *  @Security("is_granted('ROLE_ADMIN')")
    */
    public function index(ManagerRegistry $regis, $page=1): Response
    {

        $formRecherche=$this->createFormBuilder()
        ->setAction($this->generateUrl('result'))
        ->setMethod('POST')
        ->add('username', SearchType::class, ['label'=>' '])
        ->add('envoyer', SubmitType::class, ['label'=>'Rechercher un joueur'])
        ->getForm();

        $addUserByFile = $this->createFormBuilder()
        ->setAction($this->generateUrl('adduser'))
        ->setMethod('POST')
        ->add('jsonFile', FileType::class, [
            'label' => ' ',
            'mapped' => false,
            'required' => false
        ])
        ->add('sender', HiddenType::class, [

            'attr' => [
                'value' => 'jsonFile'
            ]
        ])
        ->add('envoyer', SubmitType::class, ['label' => 'Envoyer'])
        ->getForm();

        $userRepository = $regis->getRepository(Utilisateur::class);

        $query = $userRepository->createQueryBuilder('user')->getQuery();

        $paginator = new Paginator($query);

        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / 20);

        $paginator
            ->getQuery()
            ->setFirstResult(20 * ($page-1))
            ->setMaxResults(20);

        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
            'list_users' => $paginator,
            'form_user'=>$formRecherche->createView(),
            'form_file' => $addUserByFile->createView(),
            'page'=>$page,
            'nbPage'=>$pagesCount
        ]);
    }

    #[Route('/userProfile', name: 'userPrifleAPI')]
    public function userProfileAPI(Request $request, ManagerRegistry $doctrine ): Response {
        $response = new Response();
        if($request->isMethod('post')){
            $post = $request->request->all();
            $username = $post['username'];
            $userRepository = $doctrine->getRepository(Utilisateur::class);
            $user = $userRepository->findOneBy(['username'=>$username]);
            $partieRepository =  $doctrine->getRepository(Partie::class);
            $partiesJoue = $partieRepository->findBy(array('idUser' => $user->getId()));
            $tempsJoue = new \DateTime('0000-01-01 0:0:0');
            $nbWin = 0;
            foreach($partiesJoue as $partie){
                $tempsJoue->add($partie->getTemps()->format('H:i:s'));
                
                if($partie->getWin()){
                    $nbWin = $nbWin + 1;
                }
            }
      

            if($user){
                $json = array();
                $json[$username] = array();
                $json[$username]['idOrigin']=$user->getId();
                $json[$username]['parties']=count($partiesJoue);
                $json[$username]['partiesWin']=$nbWin;
                $json[$username]['tempsJoue']=$tempsJoue->format('H:i:s');
                $json[$username]['date']=$user->getDateCreation()->format('H:i:s');
            
                $jsonText = json_encode($json);
                $response->setContent($jsonText);
                $response->headers->set('Content-Type','application/json');
                $response->setStatusCode(200);
                
            }else{
                //
                $response->setStatusCode(416);
            
            }
        }else{
            $response->setStatusCode(500);
        }
        return $response;
    }

    #[Route('/user/{id}', name: 'particular_user')]
    /**
    *  @Security("is_granted('ROLE_ADMIN')")
    */
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
    /**
    *  @Security("is_granted('ROLE_ADMIN')")
    */
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
    /**
    *  @Security("is_granted('ROLE_ADMIN')")
    */
    public function banUser(ManagerRegistry $regis, $id): Response 
    {

        $em = $regis->getManager();
        $userRepository = $regis->getRepository(Utilisateur::class);
        $user = $userRepository->findOneBy(['id'=>$id]);
        if (isset($user) && $this->getUser()->getIdRole()->getRole() == "Administrateur"){
            if ($user->getIdRole()->getRole() != "Administrateur"){
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
            }
            return $this->render('user/ban.html.twig', [
                'controller_name' => 'UserController',
                'user' => $user
            ]);
        }else{
            return $this->render('user/error.html.twig', [
                'controller_name' => 'UserController'
            ]);
        }
    }

    #[Route('/loginAPI', name: 'loginAPI')]
    public function loginAPI(Request $request, ManagerRegistry $doctrine): Response {
        if($request->isMethod('post')){
            $post = $request->request->all();
            $entityManager = $doctrine->getManager();
            $userManager = $entityManager->getRepository(Utilisateur::class);

            $userCheck = $userManager->findOneBy(['username' => $post['username']]);

            //si username valide, verifie si le mdp est valide, verifie si utilisateur banni
            if ($userCheck != null){
                if ($userCheck->getIdStatut()->getId() == 3){
                    $response = new Response();
                    $response->setStatusCode(403);
                }else{
                    if (password_verify($post['mdp'], $userCheck->getMdp())) {
                        $query = $entityManager->createQueryBuilder();

                        $query->update('App\Entity\Utilisateur','user');
                        $query->set('user.idStatut',':statut');
                        $query->setParameter('statut',2);

                        $query->where('user.username LIKE :username');
                        $query->setParameter('username',$userCheck->getUsername());
        
                        $query->getQuery()->execute();

                        $response = new Response();
                        $response->setContent("{'idOrigin':'".$userCheck->getId()."', 'prenom':'".$userCheck->getPrenom()."', 'nom':'".$userCheck->getNom()."','username':'".$userCheck->getUsername()."'}");
                        $response->setStatusCode(200);
                    }else {
                        $response = new Response();
                        $response->setStatusCode(401);
                    }
                }
            }else {
                $response = new Response();
                $response->setStatusCode(416);
            }
            return $response;
        }
    }

    #[Route('/adduserAPI', name: 'adduserAPI')]
    public function addUserAPI(Request $request, ManagerRegistry $doctrine): Response {

        if($request->isMethod('post')){
            $post = $request->request->all();
            $entityManager = $doctrine->getManager();
            $roleManager = $entityManager->getRepository(Role::class);
            $statutManager = $entityManager->getRepository(Statut::class);
            $userManager = $entityManager->getRepository(Utilisateur::class);
            $roleUsager = $roleManager->findOneBy(['role' => 'Usager']);
            $statutInactif = $statutManager->findOneBy(['id' => 1]);

            $emailCheck = $userManager->findOneBy(['email' => $post['email']]);
            $usernameCheck = $userManager->findOneBy(['username' => $post['username']]);

            if ($emailCheck == null && $usernameCheck == null) {
                $user = new Utilisateur();
                    // load user data
                $user->setPrenom($post['prenom'])
                ->setNom($post['nom'])
                ->setEmail($post['email'])
                ->setUsername($post['username'])
                ->setMdp(password_hash($post['mdp'], PASSWORD_DEFAULT))
                ->setIdRole($roleUsager)
                ->setIdStatut($statutInactif)
                ->setAvatar(null)
                ->setDateCreation(date_create_from_format('Y-m-d H:i:s', date('Y-m-d H:i:s')));
                $entityManager->persist($user);
                $entityManager->flush();

                $response = new Response();
                $response->setStatusCode(200);
            } else {
                $response = new Response();
                $response->setStatusCode(416);
            }
            return $response;

        }
    }

    #[Route('/adduser', name: 'adduser')]
    public function addUser(Request $request, ManagerRegistry $doctrine): Response {
        // get post TEMP
        // $post = $request->request->all();
        // $encode = json_encode(array($post['form'], $post['form']));

        $post = $request->request->all();
        $files = $request->files->all();

        // init managers
        $entityManager = $doctrine->getManager();
        $roleManager = $entityManager->getRepository(Role::class);
        $statutManager = $entityManager->getRepository(Statut::class);
        $userManager = $entityManager->getRepository(Utilisateur::class);

        $roleUsager = $roleManager->findOneBy(['role' => 'Usager']);
        $statutInactif = $statutManager->findOneBy(['id' => 1]);

    

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

            
            // Le code que tu as ajouté
            $emailCheck = $userManager->findOneBy(['email' => $post['form']['email']]);
            $usernameCheck = $userManager->findOneBy(['username' => $post['form']['username']]);

            if ($emailCheck == null && $usernameCheck == null) {
                $user = new Utilisateur();

                // load user data
                $user->setPrenom($post['form']['prenom'])
                ->setNom($post['form']['nom'])
                ->setEmail($post['form']['email'])
                ->setUsername($post['form']['username'])
                ->setMdp(password_hash($post['form']['mdp'], PASSWORD_DEFAULT))
                ->setAvatar(null)
                ->setDateCreation(date_create_from_format('Y-m-d H:i:s', date('Y-m-d H:i:s')));

                // set role
                if (!empty($u['role']) && $roleManager->findOneBy(['id' => $post['form']['role']]) != null) {
                    $user->setIdRole($roleManager->findOneBy(['id' => $post['form']['role']]));
                }
                else {
                    $user->setIdRole($roleUsager);
                }
                
                // set statut
                if (!empty($u['statut']) && $statutManager->findOneBy(['id' => $post['form']['statut']]) != null) {
                    $user->setIdStatut($statutManager->findOneBy(['id' => $post['form']['statut']]));
                }
                else {
                    $user->setIdStatut($statutInactif);
                }

                if (!empty($u['avatar'])) {
                    $user->setAvatar($u['avatar']);
                }

                // save user
                $entityManager->persist($user);
                // jusqu'ici
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

    #[Route('/adduserfile', name: 'adduserfile')]
    public function addUserFile(Request $request, ManagerRegistry $doctrine): Response {
        return $this->render('user/index.html.twig');
    }
<<<<<<< HEAD
    
=======
    #[Route('/userProfile', name: 'userPrifleAPI')]
    public function userProfileAPI(Request $request, ManagerRegistry $doctrine ): Response {
        $response = new Response();
        if($request->isMethod('post')){
            $post = $request->request->all();
            $username = $post['username'];
            $userRepository = $doctrine->getRepository(Utilisateur::class);
            $user = $userRepository->findOneBy(['username'=>$username]);
            $partieRepository =  $doctrine->getRepository(Partie::class);
            $partiesJoue = $partieRepository->findBy(array('idUser' => $user->getId()));
            $tempsJoue = new \DateTime('0000-01-01 0:0:0');
            $nbWin = 0;
            foreach($partiesJoue as $partie){
                $tempsJoue->add($partie->getTemps()->format('H:i:s'));
                if($partie->getWin()){
                    $nbWin = $nbWin + 1;
                }
            }
            if($user){
                $json = array();
                $json[$username] = array();
                $json[$username]['idOrigin']=$user->getId();
                $json[$username]['parties']=count($partiesJoue);
                $json[$username]['partiesWin']=$nbWin;
                $json[$username]['tempsJoue']=$tempsJoue->format('H:i:s');
                $json[$username]['date']=$user->getDateCreation()->format('d/m/y');
                $json[$username]['img']= $user->getAvatar();
                $jsonText = json_encode($json);
                $response->setContent($jsonText);
                $response->headers->set('Content-Type','application/json');
                $response->setStatusCode(200);
            }else{
                //
                $response->setStatusCode(416);
            }
        }else{
            $response->setStatusCode(500);
        }
        return $response;
    }
>>>>>>> 035f50aec16d06f4e81900f570007a29e491ef29
}
