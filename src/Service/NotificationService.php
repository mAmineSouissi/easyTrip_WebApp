<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Res_transport;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class NotificationService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendReservationConfirmation(User $user, Res_transport $reservation): void
    {
        $subject = 'Confirmation de votre réservation EasyTrip';
        $from = 'mailer@vintage619.tn';
        $to = $user->getEmail();
        $text = "Bonjour " . $user->getEmail() . ",\n\n"
            . "Votre réservation a bien été confirmée !\n"
            . "Voiture : " . $reservation->getCar()->getModel() . "\n"
            . "Du : " . $reservation->getStartDate()->format('d/m/Y') . "\n"
            . "Au : " . $reservation->getEndDate()->format('d/m/Y') . "\n"
            . "Prix total : " . $reservation->getTotalPrice() . "€\n"
            . "Merci d'avoir choisi EasyTrip !";
        $html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        .header {
            background-color: #4A90E2;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            color: white;
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
            background-color: #f8f9fa;
            border-radius: 0 0 8px 8px;
        }
        .reservation-details {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .detail-item {
            margin: 10px 0;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .detail-label {
            color: #666;
            font-size: 14px;
        }
        .detail-value {
            color: #333;
            font-weight: bold;
            font-size: 16px;
        }
        .total-price {
            background-color: #4A90E2;
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>EasyTrip</h1>
        </div>
        <div class="content">
            <h2>Bonjour ' . htmlspecialchars($user->getEmail()) . ',</h2>
            <p>Votre réservation a été confirmée avec succès ! Voici les détails de votre location :</p>
            
            <div class="reservation-details">
                <div class="detail-item">
                    <div class="detail-label">Véhicule</div>
                    <div class="detail-value">' . htmlspecialchars($reservation->getCar()->getModel()) . '</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date de début</div>
                    <div class="detail-value">' . $reservation->getStartDate()->format('d/m/Y') . '</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Date de fin</div>
                    <div class="detail-value">' . $reservation->getEndDate()->format('d/m/Y') . '</div>
                </div>
            </div>

            <div class="total-price">
                <h3>Prix total</h3>
                <p style="font-size: 24px; margin: 10px 0;">' . $reservation->getTotalPrice() . ' €</p>
            </div>

            <p style="text-align: center;">
                <a href="#" class="button">Voir ma réservation</a>
            </p>

            <div class="footer">
                <p>Merci d\'avoir choisi EasyTrip pour votre location de voiture !</p>
                <p>Si vous avez des questions, n\'hésitez pas à nous contacter :</p>
                <p>Email: contact@easytrip.com | Téléphone: +33 (0)1 23 45 67 89</p>
                <p>© ' . date('Y') . ' EasyTrip. Tous droits réservés.</p>
            </div>
        </div>
    </div>
</body>
</html>';

        try {
            error_log('[EasyTrip] Attempting to send reservation confirmation email to: ' . $to);
            $email = (new Email())
                ->from($from)
                ->to($to)
                ->subject($subject)
                ->text($text)
                ->html($html);
            $this->mailer->send($email);
            error_log('[EasyTrip] Email sent successfully to: ' . $to);
        } catch (\Exception $e) {
            error_log('[EasyTrip] Failed to send email to: ' . $to . ' - Error: ' . $e->getMessage());
        }
    }
} 