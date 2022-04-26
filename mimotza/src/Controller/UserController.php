<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
use Exception;

class UserController extends AbstractController
{
    #[Route('/user', name: 'user')]
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
                        'partie'=>'6',
                        'partieWin'=>'3',
                        'temps'=>'4h',
                        'dateCreation'=>'21/04/2022',
                        'statut'=>'Banni'
                    );

        return $this->render('user/user.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user
        ]);
    }

    // DEPRECATED
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
        // get post TEMP
        // $post = $request->request->all();
        // $encode = json_encode(array($post['form'], $post['form']));

        // init managers
        $entityManager = $doctrine->getManager();
        $roleManager = $entityManager->getRepository(Role::class);
        $statutManager = $entityManager->getRepository(Statut::class);
        $userManager = $entityManager->getRepository(Utilisateur::class);

        // generate objects
        $roleUsager = $roleManager->findOneBy(['id' => 1]);
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

                // save user
                $entityManager->persist($user);
            }
        }

        // push to bd
        $entityManager->flush();        

        return $this->render('user/confirmation.html.twig', [
            'controller_name' => 'poggers',
            // 'form' => $post['form'],
        ]);
    }
}
