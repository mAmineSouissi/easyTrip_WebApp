<?php

namespace App\Controller;

use App\Entity\Hotels;
use App\Form\HotelsType;
use App\Form\HotelSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/hotels')]
class HotelsController extends AbstractController
{
    #[Route('/', name: 'app_hotels_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HotelSearchType::class);
        $form->handleRequest($request);

        $queryBuilder = $entityManager->getRepository(Hotels::class)->createQueryBuilder('h');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (!empty($data['name'])) {
                $queryBuilder->andWhere('h.name LIKE :name')->setParameter('name', '%'.$data['name'].'%');
            }
            if (!empty($data['city'])) {
                $queryBuilder->andWhere('h.city LIKE :city')->setParameter('city', '%'.$data['city'].'%');
            }
            if (!empty($data['rating'])) {
                $queryBuilder->andWhere('h.rating >= :rating')->setParameter('rating', $data['rating']);
            }
            if (!empty($data['maxPrice'])) {
                $queryBuilder->andWhere('h.price <= :maxPrice')->setParameter('maxPrice', $data['maxPrice']);
            }
            if (!empty($data['typeRoom'])) {
                $queryBuilder->andWhere('h.type_room LIKE :typeRoom')->setParameter('typeRoom', '%'.$data['typeRoom'].'%');
            }
        }

        $cities = $entityManager->getRepository(Hotels::class)
            ->createQueryBuilder('h')
            ->select('DISTINCT h.city')
            ->orderBy('h.city', 'ASC')
            ->getQuery()
            ->getResult();

        $roomTypes = $entityManager->getRepository(Hotels::class)
            ->createQueryBuilder('h')
            ->select('DISTINCT h.type_room')
            ->orderBy('h.type_room', 'ASC')
            ->getQuery()
            ->getResult();

        $hotels = $queryBuilder->getQuery()->getResult();

        return $this->render('hotels/index.html.twig', [
            'hotels' => $hotels,
            'form' => $form->createView(),
            'cities' => array_column($cities, 'city'),
            'roomTypes' => array_column($roomTypes, 'type_room'),
        ]);
    }

    #[Route('/new', name: 'app_hotels_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $hotel = new Hotels();
        $form = $this->createForm(HotelsType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $promotion = $hotel->getPromotion();
            $originalPrice = $hotel->getPrice();
            
            if ($promotion) {
                $discount = $originalPrice * ($promotion->getDiscountPercentage() / 100);
                $hotel->setPrice($originalPrice - $discount);
            }

            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('hotels_images_directory'), $newFilename);
                    $hotel->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image');
                    return $this->redirectToRoute('app_hotels_new');
                }
            }

            $entityManager->persist($hotel);
            $entityManager->flush();

            $this->addFlash('success', 'L\'hôtel a été créé avec succès');
            return $this->redirectToRoute('app_hotels_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hotels/new.html.twig', [
            'hotel' => $hotel,
            'form' => $form->createView(),
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
        $originalPromotion = $hotel->getPromotion();
        $originalPrice = $hotel->getPrice();
        
        if ($originalPromotion) {
            $originalPrice = $originalPrice / (1 - ($originalPromotion->getDiscountPercentage() / 100));
        }
        
        $form = $this->createForm(HotelsType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPromotion = $hotel->getPromotion();
            
            if ($newPromotion !== $originalPromotion) {
                if ($newPromotion) {
                    $discount = $originalPrice * ($newPromotion->getDiscountPercentage() / 100);
                    $hotel->setPrice($originalPrice - $discount);
                } else {
                    $hotel->setPrice($originalPrice);
                }
            }

            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                try {
                    $imageFile->move($this->getParameter('hotels_images_directory'), $newFilename);
                    if ($hotel->getImage()) {
                        $oldImage = $this->getParameter('hotels_images_directory').'/'.$hotel->getImage();
                        if (file_exists($oldImage)) {
                            unlink($oldImage);
                        }
                    }
                    $hotel->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image');
                    return $this->redirectToRoute('app_hotels_edit', ['id_hotel' => $hotel->getIdHotel()]);
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'L\'hôtel a été modifié avec succès');
            return $this->redirectToRoute('app_hotels_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('hotels/edit.html.twig', [
            'hotel' => $hotel,
            'form' => $form->createView(),
            'original_price' => $originalPrice,
        ]);
    }

    #[Route('/{id_hotel}', name: 'app_hotels_delete', methods: ['POST'])]
    public function delete(Request $request, Hotels $hotel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hotel->getIdHotel(), $request->request->get('_token'))) {
            if ($hotel->getImage()) {
                $imagePath = $this->getParameter('hotels_images_directory').'/'.$hotel->getImage();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $entityManager->remove($hotel);
            $entityManager->flush();
            $this->addFlash('success', 'L\'hôtel a été supprimé avec succès');
        }

        return $this->redirectToRoute('app_hotels_index', [], Response::HTTP_SEE_OTHER);
    }
}