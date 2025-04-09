<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/dashboard', 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/users', 'admin_users')]
    public function users(UserRepository $userRepo): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepo->findAll(),
        ]);
    }

    #[Route('/users/{id}', 'admin_user', methods: ['GET'])]
    public function user(User $user): JsonResponse
    {
        return $this->json([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
        ]);
    }

    #[Route('/users/create', 'admin_user_add', methods: ['POST'])]
    public function createUser(Request $request, EntityManagerInterface $em): Response
    {
        $defaultPassword = 'record';
        $request->request->set('password', password_hash($defaultPassword, PASSWORD_BCRYPT));
    
        $user = new User();
        $user->setName($request->request->get('name'));
        $user->setSurname($request->request->get('surname'));
        $user->setPhone($request->request->get('phone'));
        $user->setAddresse($request->request->get('address')); // corrected spelling
        $user->setProfilePhoto(''); // safe default if not used yet
        $user->setPassword($request->request->get('password'));
        $user->setEmail($request->request->get('email'));
        $user->setRole($request->request->get('role'));
    
        $em->persist($user);
        $em->flush();
    
        return new JsonResponse(['status' => 'User created'], 201);
    }

    #[Route('/users/{id}/edit/confirm', 'admin_user_edit_confirm', methods: ['POST'])]
    public function editUserConfirm(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $user->setName($request->request->get('name'));
        $user->setEmail($request->request->get('email'));
        $user->setPhone($request->request->get('phone'));
        $user->setAddresse($request->request->get('address')); 
        $user->setRole($request->request->get('role'));

        $em->flush();

        return new JsonResponse(['status' => 'User updated']);
    }

    #[Route('/users/{id}/delete/confirm', 'admin_user_delete_confirm', methods: ['POST'])]
    public function deleteUserConfirm(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();

        return new JsonResponse(['status' => 'User deleted']);
    }

    #[Route('/settings', 'admin_settings')]
    public function settings(): Response
    {
        return $this->render('admin/settings.html.twig');
    }
}
