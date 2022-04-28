<?php
/****************************************
Fichier : SecurityController.php
Auteurs : François-Nicolas Gitzhofer
Fonctionnalité : Classe SecurityController qui permet d'afficher le formulaire de connexion.
Date : 21/04/2022
Vérification :
Date Nom Approuvé
=========================================================
Historique de modifications (Approximatif) :
Date: 25/04/2022 Nom: François-Nicolas Gitzhofer Description: Création du contrôleur en utilisant les commandes sur powershell
Date: 26/04/2022 Nom: François-Nicolas Gitzhofer Description: Ajout de la fonction de redirection au début de login
Date: 27/04/2022 Nom: François-Nicolas Gitzhofer Description: Amélioration de la fonction de redirection au début de login
...
=========================================================
****************************************/
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {

            return $this->redirectToRoute('app_eventRedirect', [
                'userId' => $this->getUser()->getId(),
                'eventType' => 2,
                'whereTo' => '|'
            ]);
        }

        $post = $request->request->all();

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'post' => $post
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): Response
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
