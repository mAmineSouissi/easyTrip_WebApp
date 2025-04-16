<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Get login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // Get last entered username (email)
        $lastUsername = $authenticationUtils->getLastUsername();
        // If the user is already logged in, redirect to the appropriate dashboard
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        } elseif ($this->isGranted('ROLE_AGENT')) {
            return $this->redirectToRoute('agent_dashboard');
        } elseif ($this->isGranted('ROLE_CLIENT')) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
