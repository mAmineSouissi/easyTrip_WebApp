<?php

namespace App\Controller;

use App\Entity\OfferTravel;
use App\Form\OfferTravelType;
use App\Repository\Offer_travelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/offer/travel')]
final class OfferTravelController extends AbstractController
{
    #[Route(name: 'app_offer_travel_index', methods: ['GET'])]
    public function index(Offer_travelRepository $offer_travelRepository): Response
    {
        return $this->render('offer_travel/index.html.twig', [
            'offer_travels' => $offer_travelRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_offer_travel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $offerTravel = new OfferTravel();
        $form = $this->createForm(OfferTravelType::class, $offerTravel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($offerTravel);
            $entityManager->flush();

            return $this->redirectToRoute('app_offer_travel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('offer_travel/new.html.twig', [
            'offer_travel' => $offerTravel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_offer_travel_show', methods: ['GET'])]
    public function show(OfferTravel $offerTravel): Response
    {
        return $this->render('offer_travel/show.html.twig', [
            'offer_travel' => $offerTravel,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_offer_travel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OfferTravel $offerTravel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OfferTravelType::class, $offerTravel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_offer_travel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('offer_travel/edit.html.twig', [
            'offer_travel' => $offerTravel,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_offer_travel_delete', methods: ['POST'])]
    public function delete(Request $request, OfferTravel $offerTravel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offerTravel->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($offerTravel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_offer_travel_index', [], Response::HTTP_SEE_OTHER);
    }
}
