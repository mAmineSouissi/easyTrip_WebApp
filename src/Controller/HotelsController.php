<?php

namespace App\Controller;

use App\Entity\Hotels;
use App\Form\HotelsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/hotels')]
class HotelsController extends AbstractController
{
    #[Route('/', name: 'app_hotels_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $hotels = $entityManager
            ->getRepository(Hotels::class)
            ->findAll();

        return $this->render('hotels/index.html.twig', [
            'hotels' => $hotels,
        ]);
    }

    #[Route('/new', name: 'app_hotels_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hotel = new Hotels();
        $form = $this->createForm(HotelsType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($hotel);
            $entityManager->flush();

            return $this->redirectToRoute('app_hotels_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hotels/new.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    #[Route('/{id_hotel}', name: 'app_hotels_show', methods: ['GET'])]
    public function show(Hotels $hotel): Response
    {
        return $this->render('hotels/show.html.twig', [
            'hotel' => $hotel,
        ]);
    }

    #[Route('/{id_hotel}/edit', name: 'app_hotels_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hotels $hotel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HotelsType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_hotels_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hotels/edit.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    #[Route('/{id_hotel}', name: 'app_hotels_delete', methods: ['POST'])]
    public function delete(Request $request, Hotels $hotel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hotel->getIdHotel(), $request->request->get('_token'))) {
            $entityManager->remove($hotel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_hotels_index', [], Response::HTTP_SEE_OTHER);
    }
}