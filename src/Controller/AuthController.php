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
    
            // Hash the password
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            );
            $user->setPassword($hashedPassword);
    
            // Handle profile photo upload
            $profilePhotoFile = $form->get('profilePhoto')->getData();
    
            if ($profilePhotoFile) {
                // Generate a unique filename to avoid collisions
                $originalFilename = pathinfo($profilePhotoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $originalFilename);
                $extension = $profilePhotoFile->guessExtension();
                $newFilename = uniqid() . '-' . $safeFilename . '.' . $extension;
                
                $profilesDirectory = $this->getParameter('profiles_directory');
                
                try {
                    $profilePhotoFile->move($profilesDirectory, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload profile photo: ' . $e->getMessage());
                    return $this->redirectToRoute('app_register');
                }
    
                // Absolute path to uploaded photo
                $photoAbsolutePath = $profilesDirectory . '/' . $newFilename;
    
                // Run face detection
                $hasFace = $this->detectFace($photoAbsolutePath);
                
                if (!$hasFace) {
                    // Clean up the file if no face detected
                    if (file_exists($photoAbsolutePath)) {
                        unlink($photoAbsolutePath);
                    }
                    $this->addFlash('error', 'The profile photo must clearly show a face.');
                    return $this->redirectToRoute('app_register');
                }
    
                // Save the relative path for the database
                // This assumes your web server is configured to serve files from 'profiles_directory'
                // under the '/img/profile/' URL path
                $photoUrl = '/img/profile/' . $newFilename;
                $user->setProfilePhoto($photoUrl);
            }
    
            $em->persist($user);
            $em->flush();
    
            $this->addFlash('success', 'Registration successful!');
            return $this->redirectToRoute('app_home');
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
