<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class MailController extends AbstractController
{
    #[Route('/send-test-email', name: 'send_test_email')]
    public function sendTestEmail(MailerInterface $mailer, LoggerInterface $logger): Response
    {
        try {
            $email = (new Email())
                ->from('youssefcarma@gmail.com')
                ->to('mohamedyoussefazzouz@gmail.com')
                ->subject('Test Mailtrap')
                ->text('Email de test')
                ->html('<p>Email de <strong>test</strong></p>');

            $mailer->send($email);
            $logger->info('Email sent successfully');
            return new Response('Email envoyé !');

        } catch (\Throwable $e) {
            $logger->error('Email error: '.$e->getMessage());
            return new Response('Erreur: '.$e->getMessage(), 500);
        }
    }
}