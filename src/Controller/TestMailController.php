<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class TestMailController extends AbstractController
{
    #[Route('/test-email', name: 'app_test_email')]
    public function sendTestEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('oussamabani65@gmail.com')
            ->to('oussamabani14@gmail.com') // Remplacez par un vrai email
            ->subject('Test Email')
            ->text('Ceci est un email test depuis Symfony');

        try {
            $mailer->send($email);
            return new Response('Email envoyé avec succès!');
        } catch (\Exception $e) {
            return new Response('Erreur: '.$e->getMessage());
        }
    }
}