<?php

namespace App\Controller;

use App\Entity\Verification_codes;
use App\Entity\VerificationCodes;
use App\Repository\UserRepository;
use App\Repository\Verification_codesRepository;
use App\Repository\VerificationCodesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepo,
        private Verification_codesRepository $codeRepo
    ) {}

    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $user = $this->userRepo->getUserByEmail($email);

            if (!$user) {
                $this->addFlash('danger', 'No user found with that email.');
                return $this->redirectToRoute('app_forgot_password');
            }

            // Generate 6-digit code
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Store the code
            $verifCode = new Verification_codes();
            $verifCode->setEmail($email);
            $verifCode->setCode($code);
            $verifCode->setCreated_at(new \DateTime());
            $verifCode->setExpires_at(new \DateTime('+15 minutes'));
            $verifCode->setUsed(0);

            $this->em->persist($verifCode);
            $this->em->flush();

            // Send email
            $emailMessage = (new TemplatedEmail())
                ->from(new Address('aminesouissi681@gmail.com', 'EasyTrip'))
                ->to($email)
                ->subject('Reset your password')
                ->htmlTemplate('emails/reset_code.html.twig')
                ->context([
                    'code' => $code,
                    'user' => $user,
                ]);

            try {
                $mailer->send($emailMessage);
                $this->addFlash('success', 'Verification code sent!');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Email sending error: ' . $e->getMessage());
                return $this->redirectToRoute('app_forgot_password');
            }

            return $this->redirectToRoute('app_verify_code', ['email' => $email]);
        }

        return $this->render('security/forgot_password.html.twig');
    }

    #[Route('/verify-code/{email}', name: 'app_verify_code')]
    public function verifyCode(Request $request, string $email): Response
    {
        if ($request->isMethod('POST')) {
            $enteredCode = $request->request->get('code');
            $code = $this->codeRepo->findValidCode($email, $enteredCode);

            if (!$code) {
                $this->addFlash('danger', 'Invalid or expired code.');
                return $this->redirectToRoute('app_verify_code', ['email' => $email]);
            }

            $code->setUsed(1);
            $this->em->flush();

            $this->codeRepo->removeUsedCode($code);
            $session = $request->getSession();
            $session->set('can_reset_' . $email, true); // Allow password reset for this email
            $session->set('email', $email); // Store email in session
            $this->addFlash('success', 'Code verified! You can now reset your password.');

            return $this->redirectToRoute('app_reset_password', ['email' => $email]);
        }

        return $this->render('security/verify_code.html.twig', ['email' => $email]);
    }

    #[Route('/reset-password/{email}', name: 'app_reset_password')]
    public function resetPassword(Request $request, string $email, UserPasswordHasherInterface $passwordHasher): Response
    {
        $session = $request->getSession();

        // Check if the session allows resetting this email's password
        if (!$session->get('can_reset_' . $email)) {
            $this->addFlash('danger', 'Unauthorized access to reset page.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $user = $this->userRepo->findOneBy(['email' => $email]);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('POST')) {
            $pass = $request->request->get('password');
            $confirm = $request->request->get('confirm_password');

            if ($pass !== $confirm) {
                $this->addFlash('danger', 'Passwords do not match.');
                return $this->redirectToRoute('app_reset_password', ['email' => $email]);
            }

            $user->setPassword($passwordHasher->hashPassword($user, $pass));
            $this->em->flush();

            $this->addFlash('success', 'Password successfully reset.');
            $session->remove('can_reset_' . $email); 

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', ['email' => $email]);
    }
}
