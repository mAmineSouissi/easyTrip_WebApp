<?php

namespace App\Controller;

use App\Entity\Hotels;
use App\Entity\Agency;
use App\Form\HotelsType;
use App\Form\HotelSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

#[Route('/hotels')]
class HotelsController extends AbstractController
{
    private $mailer;
    private $logger;
    private $entityManager;

    public function __construct(
        MailerInterface $mailer, 
        LoggerInterface $logger,
        EntityManagerInterface $entityManager
    ) {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_hotels_index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(HotelSearchType::class);
        $form->handleRequest($request);

        $queryBuilder = $this->entityManager->getRepository(Hotels::class)
            ->createQueryBuilder('h');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            if (!empty($data['name'])) {
                $queryBuilder->andWhere('h.name LIKE :name')
                    ->setParameter('name', '%'.$data['name'].'%');
            }
            if (!empty($data['city'])) {
                $queryBuilder->andWhere('h.city LIKE :city')
                    ->setParameter('city', '%'.$data['city'].'%');
            }
            if (!empty($data['rating'])) {
                $queryBuilder->andWhere('h.rating >= :rating')
                    ->setParameter('rating', $data['rating']);
            }
            if (!empty($data['maxPrice'])) {
                $queryBuilder->andWhere('h.price <= :maxPrice')
                    ->setParameter('maxPrice', $data['maxPrice']);
            }
            if (!empty($data['typeRoom'])) {
                $queryBuilder->andWhere('h.type_room LIKE :typeRoom')
                    ->setParameter('typeRoom', '%'.$data['typeRoom'].'%');
            }
        }

        $cities = $this->entityManager->getRepository(Hotels::class)
            ->createQueryBuilder('h')
            ->select('DISTINCT h.city')
            ->orderBy('h.city', 'ASC')
            ->getQuery()
            ->getResult();

        $roomTypes = $this->entityManager->getRepository(Hotels::class)
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
    public function new(Request $request): Response
    {
        $hotel = new Hotels();
        $form = $this->createForm(HotelsType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $imageFile = $form->get('image')->getData();
                if ($imageFile) {
                    $newFilename = uniqid().'.'.$imageFile->guessExtension();
                    $imageFile->move(
                        $this->getParameter('hotels_images_directory'),
                        $newFilename
                    );
                    $hotel->setImage($newFilename);
                }

                // Treat form price as original price and apply discount if promotion exists
                $originalPrice = $form->get('price')->getData();
                if ($hotel->getPromotion() && $hotel->getPromotion()->getDiscountPercentage() > 0) {
                    $discountPercentage = $hotel->getPromotion()->getDiscountPercentage();
                    $discountedPrice = $originalPrice * (1 - $discountPercentage / 100);
                    $hotel->setPrice($discountedPrice);
                } else {
                    $hotel->setPrice($originalPrice);
                }

                $this->entityManager->persist($hotel);
                $this->entityManager->flush();

                $this->addFlash('success', 'Hôtel créé avec succès !');
                return $this->redirectToRoute('app_hotels_index');

            } catch (\Exception $e) {
                $this->logger->error('Erreur création hôtel: '.$e->getMessage());
                $this->addFlash('error', 'Erreur lors de la création: '.$e->getMessage());
            }
        }

        return $this->render('hotels/new.html.twig', [
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
    public function edit(Request $request, Hotels $hotel): Response
    {
        $form = $this->createForm(HotelsType::class, $hotel);
        $form->handleRequest($request);

        // Calculate original price for display
        $originalPrice = $hotel->getPrice();
        if ($hotel->getPromotion()) {
            $discountPercentage = $hotel->getPromotion()->getDiscountPercentage();
            if ($discountPercentage > 0) {
                $originalPrice = $hotel->getPrice() / (1 - $discountPercentage / 100);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $imageFile = $form->get('image')->getData();
                if ($imageFile) {
                    // Delete old image if exists
                    if ($hotel->getImage()) {
                        $oldImagePath = $this->getParameter('hotels_images_directory').'/'.$hotel->getImage();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    // Upload new image
                    $newFilename = uniqid().'.'.$imageFile->guessExtension();
                    $imageFile->move(
                        $this->getParameter('hotels_images_directory'),
                        $newFilename
                    );
                    $hotel->setImage($newFilename);
                }

                // Treat form price as original price and apply discount if promotion exists
                $originalPrice = $form->get('price')->getData();
                if ($hotel->getPromotion() && $hotel->getPromotion()->getDiscountPercentage() > 0) {
                    $discountPercentage = $hotel->getPromotion()->getDiscountPercentage();
                    $discountedPrice = $originalPrice * (1 - $discountPercentage / 100);
                    $hotel->setPrice($discountedPrice);
                } else {
                    $hotel->setPrice($originalPrice);
                }

                $this->entityManager->flush();
                $this->addFlash('success', 'Hôtel modifié avec succès');
                return $this->redirectToRoute('app_hotels_index');
            } catch (\Exception $e) {
                $this->logger->error('Erreur modification: '.$e->getMessage());
                $this->addFlash('error', 'Erreur lors de la modification: '.$e->getMessage());
            }
        }

        return $this->render('hotels/edit.html.twig', [
            'hotel' => $hotel,
            'form' => $form->createView(),
            'original_price' => $originalPrice,
        ]);
    }

    #[Route('/{id_hotel}', name: 'app_hotels_delete', methods: ['POST'])]
    public function delete(Request $request, Hotels $hotel): Response
    {
        if ($this->isCsrfTokenValid('delete'.$hotel->getId(), $request->request->get('_token'))) {
            try {
                if ($hotel->getImage()) {
                    $imagePath = $this->getParameter('hotels_images_directory').'/'.$hotel->getImage();
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }

                $this->entityManager->remove($hotel);
                $this->entityManager->flush();
                $this->addFlash('success', 'Hôtel supprimé avec succès');
            } catch (\Exception $e) {
                $this->logger->error('Erreur suppression: '.$e->getMessage());
                $this->addFlash('error', 'Erreur lors de la suppression: '.$e->getMessage());
            }
        }

        return $this->redirectToRoute('app_hotels_index');
    }
}