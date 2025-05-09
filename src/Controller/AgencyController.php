<?php

namespace App\Controller;

use App\Entity\Agency;
use App\Form\AgencyType;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/agency')]
final class AgencyController extends AbstractController
{
    private string $baseImageUrl = 'http://localhost:8000/img/agency/';

    private function getUserRole(User $user): string
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true) ? 'admin' : 'agent';
    }

    #[Route(name: 'app_agency_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $role = $this->getUserRole($user);

        // If admin, fetch all agencies; if agent, fetch only the user's agencies
        $agencies = $role === 'admin'
            ? $entityManager->getRepository(Agency::class)->findAll()
            : $entityManager->getRepository(Agency::class)->findBy(['user' => $user]);

        return $this->render("agency/{$role}/index.html.twig", [
            'agencies' => $agencies,
        ]);
    }

    #[Route('/new', name: 'app_agency_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $role = $this->getUserRole($user);

        $agency = new Agency();
        
        // Définir l'utilisateur connecté
        if ($user) {
            $agency->setUser($user);
        } else {
            $this->addFlash('error', 'Aucun utilisateur connecté');
            return $this->redirectToRoute('app_agency_index');
        }

        $form = $this->createForm(AgencyType::class, $agency);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            dump($form->getData());
            dump($form->isValid() ? 'Formulaire valide' : 'Formulaire invalide');
            dump($form->getErrors(true, true));

            if ($form->isValid()) {
                try {
                    // Gérer l'upload de l'image
                    $imageFile = $form->get('imageFile')->getData();
                    if ($imageFile instanceof UploadedFile) {
                        $newFilename = uniqid().'.'.$imageFile->guessExtension();
                        try {
                            $imageFile->move(
                                $this->getParameter('agency_images_directory'),
                                $newFilename
                            );
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : '.$e->getMessage());
                            return $this->render("agency/{$role}/new.html.twig", [
                                'form' => $form->createView(),
                            ]);
                        }
                        // Construire le chemin complet avec l'URL de base
                        $imagePath = $this->baseImageUrl . $newFilename;
                        $agency->setImage($imagePath);
                    }

                    $em->persist($agency);
                    $em->flush();

                    $this->addFlash('success', 'Agence créée avec succès ! ID: '.$agency->getId());
                    return $this->redirectToRoute('app_agency_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur technique : '.$e->getMessage());
                    dump($e->getMessage());
                }
            } else {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', 'Erreur de validation : '.$error->getMessage());
                }
            }
        }

        return $this->render("agency/{$role}/new.html.twig", [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_agency_show', methods: ['GET'])]
    public function show(Agency $agency): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $role = $this->getUserRole($user);

        // Agents can only view their own agencies
        if ($role === 'agent' && $agency->getUser() !== $user) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à voir cette agence.');
            return $this->redirectToRoute('app_agency_index');
        }

        return $this->render("agency/{$role}/show.html.twig", [
            'agency' => $agency,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_agency_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Agency $agency, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $role = $this->getUserRole($user);

        // Agents can only edit their own agencies
        if ($role === 'agent' && $agency->getUser() !== $user) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cette agence.');
            return $this->redirectToRoute('app_agency_index');
        }

        // S'assurer que l'utilisateur est bien connecté
        if ($user) {
            $agency->setUser($user);
        } else {
            $this->addFlash('error', 'Aucun utilisateur connecté');
            return $this->redirectToRoute('app_agency_index');
        }

        $form = $this->createForm(AgencyType::class, $agency);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    // Gérer l'upload de l'image
                    $imageFile = $form->get('imageFile')->getData();
                    if ($imageFile instanceof UploadedFile) {
                        $newFilename = uniqid().'.'.$imageFile->guessExtension();
                        try {
                            $imageFile->move(
                                $this->getParameter('agency_images_directory'),
                                $newFilename
                            );
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : '.$e->getMessage());
                            return $this->render("agency/{$role}/edit.html.twig", [
                                'form' => $form->createView(),
                                'agency' => $agency,
                            ]);
                        }
                        // Supprimer l'ancienne image si elle existe
                        if ($agency->getImage()) {
                            $oldFilename = basename($agency->getImage());
                            $oldImagePath = $this->getParameter('agency_images_directory').'/'.$oldFilename;
                            if (file_exists($oldImagePath)) {
                                unlink($oldImagePath);
                            }
                        }
                        // Construire le nouveau chemin complet
                        $imagePath = $this->baseImageUrl . $newFilename;
                        $agency->setImage($imagePath);
                    }

                    $em->flush();
                    $this->addFlash('success', 'Agence mise à jour avec succès !');
                    return $this->redirectToRoute('app_agency_show', ['id' => $agency->getId()]);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de la mise à jour : '.$e->getMessage());
                }
            } else {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', 'Erreur de validation : '.$error->getMessage());
                }
            }
        }

        return $this->render("agency/{$role}/edit.html.twig", [
            'form' => $form->createView(),
            'agency' => $agency,
        ]);
    }

    #[Route('/{id}', name: 'app_agency_delete', methods: ['POST'])]
    public function delete(Request $request, Agency $agency, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $role = $this->getUserRole($user);

        // Agents can only delete their own agencies
        if ($role === 'agent' && $agency->getUser() !== $user) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à supprimer cette agence.');
            return $this->redirectToRoute('app_agency_index');
        }

        if ($this->isCsrfTokenValid('delete'.$agency->getId(), $request->getPayload()->getString('_token'))) {
            // Supprimer l'image associée
            if ($agency->getImage()) {
                $filename = basename($agency->getImage());
                $imagePath = $this->getParameter('agency_images_directory').'/'.$filename;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            $entityManager->remove($agency);
            $entityManager->flush();
            $this->addFlash('success', 'Agence supprimée avec succès !');
        }

        return $this->redirectToRoute('app_agency_index', [], Response::HTTP_SEE_OTHER);
    }
}