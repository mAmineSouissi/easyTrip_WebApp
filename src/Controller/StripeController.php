<?php

namespace App\Controller;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Repository\ReservationRepository;
use Dompdf\Dompdf;


class StripeController extends AbstractController
{
    #[Route('/create-checkout-session', name: 'create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']); 

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Payer votre réservation',
                    ],
                    'unit_amount' => 150000, 
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('payment_success', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('payment_cancel', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return new JsonResponse(['id' => $session->id]);
    }

    #[Route('/payment-success', name: 'payment_success')]
    public function paymentSuccess(MailerInterface $mailer, ReservationRepository $reservationRepository): Response
    {
    $reservations = $reservationRepository->findAll();
    $prixUnitaire = 50.00;
    $total = 0;

    foreach ($reservations as $reservation) {
        $total += $prixUnitaire * $reservation->getPlaces();
    }
    // 1. Générer HTML à partir du template
    $html = $this->renderView('reservation/facture.html.twig', [
        'reservations' => $reservations,
        'total' => $total,
    ]);

    // 2. Générer le PDF avec Dompdf
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdfOutput = $dompdf->output();

    // 3. Créer l'email
    $email = (new Email())
        ->from('boussaksonia@gmail.com') 
        ->to('benbrahemabir@gmail.com')    
        ->subject('Confirmation de paiement')
        ->html('
            <div style="background-color: #f9f9f9; padding: 20px; border-radius: 8px; font-family: Arial, sans-serif; color: #333;">
                <h1 style="color: #4CAF50;">Paiement confirmé !</h1>
                <p>Bonjour,</p>
                <p>Nous vous confirmons que votre paiement a été <strong>réalisé avec succès</strong>.</p>
                <p>Vous trouverez votre facture en pièce jointe.</p>
                <p>Merci pour votre confiance et à très bientôt !</p>
            </div>
        ')
        ->attach($pdfOutput, 'facture.pdf', 'application/pdf'); // ATTACHER LE PDF

    // 4. Envoyer l'email
    $mailer->send($email);

    return new Response('<h1>Paiement réussi ! Un e-mail de confirmation avec facture a été envoyé.</h1>');
   }



    #[Route('/payment-cancel', name: 'payment_cancel')]
    public function paymentCancel(): Response
    {
    return new Response('<h1>Paiement annulé.</h1>');
    }

    #[Route('/payer', name: 'stripe_checkout')]
    public function index(): Response
    {
        return $this->render('stripe/index.html.twig', [
        'STRIPE_PUBLIC_KEY' => $_ENV['STRIPE_PUBLIC_KEY']
    ]);
    }

}