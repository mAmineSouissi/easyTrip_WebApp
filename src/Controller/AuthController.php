<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Psr\Log\LoggerInterface;
class AuthController extends AbstractController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        ManagerRegistry $doctrine

    ): Response {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            );
            $user->setPassword($hashedPassword);

            $profilePhotoFile = $form->get('profilePhoto')->getData();

            if ($profilePhotoFile) {
                $newFilename = $profilePhotoFile->getClientOriginalName();

                try {
                    $profilePhotoFile->move(
                        $this->getParameter('profiles_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload profile photo.');
                    return $this->redirectToRoute('app_register');
                }
                $baseUrl = "http://localhost"; 
                $photoUrl = $baseUrl . '/img/profile/' . $newFilename;

                $user->setProfilePhoto($photoUrl);

                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('app_home');
            }

            return $this->render('auth/register.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        return $this->render('auth/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * Detects if there's a face in the given image
     */
    private function detectFace(string $imagePath): bool
    {
        // In Docker environment, the path needs to be the container's internal path
        $command = "python3 /var/www/detect_face.py " . escapeshellarg($imagePath) . " 2>&1";
        
        exec($command, $output, $returnCode);
        
        // Check if the command ran successfully
        if ($returnCode !== 0) {
            $this->logger->error('Face detection failed', [
                'command' => $command,
                'output' => $output,
                'returnCode' => $returnCode
            ]);
            return false;
        }
        
        // Check if any line of output contains FACE_FOUND
        foreach ($output as $line) {
            if (trim($line) === 'FACE_FOUND') {
                return true;
            }
        }
        
        return false;
    }

    
    #[Route('/redirect', name: 'app_redirect_after_login')]
    public function redirectAfterLogin(): Response
    {
        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        }

        if ($this->isGranted('ROLE_AGENT')) {
            return $this->redirectToRoute('agent_dashboard');
        }

        return $this->redirectToRoute('app_home');
    }
}
