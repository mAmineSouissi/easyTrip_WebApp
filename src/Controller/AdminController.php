<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
final class AdminController extends AbstractController
{
    #[Route('/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/users', name: 'admin_users')]
    public function users(UserRepository $userRepo): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepo->findAll(),
        ]);
    }

    #[Route('/users/create', name: 'admin_user_add')]
    public function createUser(): Response
    {
        // Render a form for creating users if needed (not covered in modal)
        return $this->render('admin/create_user.html.twig');
    }

    #[Route('/users/{id}/edit', name: 'admin_user_edit', methods: ['POST'])]
    public function editUser(Request $request, int $id, UserRepository $userRepo, EntityManagerInterface $em): RedirectResponse
    {
        $user = $userRepo->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $user->setName($request->request->get('name'));
        $user->setEmail($request->request->get('email'));
        $user->setRole($request->request->get('role'));

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(Request $request, int $id, UserRepository $userRepo, EntityManagerInterface $em): RedirectResponse
    {
        $user = $userRepo->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('admin_users');
    }

    #[Route('/settings', name: 'admin_settings')]
    public function settings(): Response
    {
        return $this->render('admin/settings.html.twig');
    }
}