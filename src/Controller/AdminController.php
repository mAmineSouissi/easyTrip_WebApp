<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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

    // In AdminController.php
    #[Route('/chart/users-by-role', 'admin_chart_users_by_role')]
    public function getUsersByRoleChart(UserRepository $userRepo): JsonResponse
    {
        $users = $userRepo->findAll();
        $roleStats = [];

        foreach ($users as $user) {
            $role = $user->getRole();
            if (!isset($roleStats[$role])) {
                $roleStats[$role] = 0;
            }
            $roleStats[$role]++;
        }

        // Format for chart data
        $labels = array_keys($roleStats);
        $data = array_values($roleStats);

        return $this->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Users by Role',
                    'data' => $data,
                    'backgroundColor' => [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ]
                ]
            ]
        ]);
    }

    #[Route('/users', 'admin_users')]
    public function users(UserRepository $userRepo): Response
    {
        return $this->render('admin/users.html.twig', [
            'users' => $userRepo->findAll(),
        ]);
    }

    #[Route('/profile/{id}', name: 'profile_update')]
    public function updateProfile(Request $request, EntityManagerInterface $em, $id)
    {
        /** @var User $user*/
        $currentuser = $this->getUser();
        $user = $em->getRepository(User::class)->find($id);

        $form = $this->createForm(ProfileType::class, $currentuser);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $profilePhotoFile = $form->get('profilePhoto')->getData();
            if ($profilePhotoFile) {
                $newFilename = $profilePhotoFile->getClientOriginalName(); 

                try {
                    $profilePhotoFile->move(
                        $this->getParameter('profiles_directory'), 
                        $newFilename
                    );
                } catch (FileException $e) {
                    // handle exception
                }

                $baseUrl = $request->getSchemeAndHttpHost();
                $photoUrl = $baseUrl . '/img/profile/' . $newFilename;
                $user->setProfilePhoto($photoUrl);
            }

            $em->flush();

            $this->addFlash('success', 'Profile updated successfully!');

            if ($currentuser->getRoles()[0] === 'ROLE_ADMIN') {
                return $this->redirectToRoute('admin_dashboard');
            } else {
                return $this->redirectToRoute('app_home');
            }
        }

        return $this->render('admin/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/users/{id}', 'admin_user', methods: ['GET'])]
    public function user(User $user): JsonResponse
    {
        return $this->json([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'phone' => $user->getPhone(),
            'surname' => $user->getSurname(),
            'address' => $user->getAddresse(),
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
        $user->setSurname($request->request->get('surname'));
        $user->setAddresse($request->request->get('address'));
        $user->setRole($request->request->get('role'));

        $em->flush();

        return new JsonResponse(['status' => 'User updated']);
    }

    #[Route('/users/{id}/delete/confirm', 'admin_user_delete_confirm', methods: ['POST'])]
    public function deleteUserConfirm(Request $request, User $user, EntityManagerInterface $em, Security $security): Response
    {
        /** @var User $currentUser */

        $currentUser = $security->getUser();

        if ($user->getId() === $currentUser->getId()) {
            return new JsonResponse(['status' => 'You cannot delete yourself'], 403);
        }

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
