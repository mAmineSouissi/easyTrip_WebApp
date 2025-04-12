<?php

namespace App\Controller;

use App\Entity\OfferTravel;
use App\Form\OfferTravelType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/offer/travel')]
class OfferTravelController extends AbstractController
{
    private string $baseImageUrl = 'http://localhost:8000/img/offers/';

    #[Route('/', name: 'app_offer_travel_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $offers = $entityManager
            ->getRepository(OfferTravel::class)
            ->findAll();

        return $this->render('offer_travel/index.html.twig', [
            'offer_travels' => $offers,
        ]);
    }

    #[Route('/new', name: 'app_offer_travel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $offerTravel = new OfferTravel();
        $form = $this->createForm(OfferTravelType::class, $offerTravel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('offers_images_directory'),
                        $newFilename
                    );
                    $offerTravel->setImage($this->baseImageUrl . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            }

            $em->persist($offerTravel);
            $em->flush();

            $this->addFlash('success', 'Offre créée avec succès !');
            return $this->redirectToRoute('app_offer_travel_index');
        }

        return $this->render('offer_travel/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_offer_travel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OfferTravel $offerTravel, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(OfferTravelType::class, $offerTravel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'image
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                // Supprimer ancienne image si elle existe
                if ($offerTravel->getImage()) {
                    $oldFilename = basename($offerTravel->getImage());
                    $oldPath = $this->getParameter('offers_images_directory').'/'.$oldFilename;
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('offers_images_directory'),
                        $newFilename
                    );
                    $offerTravel->setImage($this->baseImageUrl . $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            }

            $em->flush();

            $this->addFlash('success', 'Offre modifiée avec succès !');
            return $this->redirectToRoute('app_offer_travel_index');
        }

        return $this->render('offer_travel/edit.html.twig', [
            'form' => $form->createView(),
            'offer_travel' => $offerTravel,
        ]);
    }

    #[Route('/{id}', name: 'app_offer_travel_show', methods: ['GET'])]
    public function show(OfferTravel $offerTravel): Response
    {
        return $this->render('offer_travel/show.html.twig', [
            'offer_travel' => $offerTravel,
        ]);
    }

    #[Route('/{id}', name: 'app_offer_travel_delete', methods: ['POST'])]
    public function delete(Request $request, OfferTravel $offerTravel, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$offerTravel->getId(), $request->request->get('_token'))) {
            // Supprimer l'image associée
            if ($offerTravel->getImage()) {
                $filename = basename($offerTravel->getImage());
                $imagePath = $this->getParameter('offers_images_directory').'/'.$filename;
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $em->remove($offerTravel);
            $em->flush();
            
            $this->addFlash('success', 'Offre supprimée avec succès !');
        }

        return $this->redirectToRoute('app_offer_travel_index');
    }
}