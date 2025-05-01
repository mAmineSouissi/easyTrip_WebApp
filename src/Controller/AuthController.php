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

class AuthController extends AbstractController
{
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
            // Keep the original name, but make sure it won't overwrite existing files
            $originalFilename = pathinfo($profilePhotoFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $originalFilename);
            $extension = $profilePhotoFile->guessExtension();
            $newFilename = $safeFilename . '.' . $extension;

            $uploadPath = $this->getParameter('profiles_directory') . '/' . $newFilename;

            // If file already exists, add a random prefix
            if (file_exists($uploadPath)) {
                $newFilename = uniqid() . '-' . $newFilename;
                $uploadPath = $this->getParameter('profiles_directory') . '/' . $newFilename;
            }

            try {
                $profilePhotoFile->move(
                    $this->getParameter('profiles_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                $this->addFlash('error', 'Failed to upload profile photo.');
                return $this->redirectToRoute('app_register');
            }

            // Absolute path to uploaded photo
            $photoAbsolutePath = $this->getParameter('profiles_directory') . '/' . $newFilename;

            // Define the command to run the Python face detection script
            $projectDir = $this->getParameter('kernel.project_dir');
            $pythonScriptPath = $projectDir . '/scripts/detect_face.py';
            $command = escapeshellcmd("python3 $pythonScriptPath '$photoAbsolutePath'");

            exec($command, $output, $returnCode);

            if (!in_array('FACE_FOUND', array_map('trim', $output))) {
                unlink($photoAbsolutePath);
                $this->addFlash('error', 'The profile photo must clearly show a face.');
                return $this->redirectToRoute('app_register');
            }

            // Save full image URL
            $baseUrl = "http://localhost"; // http://localhost
            $photoUrl = $baseUrl . '/img/profile/' . $originalFilename . '.' . $extension;
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
