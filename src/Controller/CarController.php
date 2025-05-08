<?php

namespace App\Controller;

use App\Entity\Cars;
use App\Form\CarType;
use App\Service\MapboxService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cars')]
class CarController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private MapboxService $mapboxService;

    public function __construct(EntityManagerInterface $entityManager, MapboxService $mapboxService)
    {
        $this->entityManager = $entityManager;
        $this->mapboxService = $mapboxService;
    }

    #[Route('/', name: 'cars_list')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $search = $request->query->get('search', '');
        $seats = $request->query->get('seats', '');
        $priceRange = $request->query->get('priceRange', '');

        $qb = $entityManager->createQueryBuilder();
        $qb->select('c')
           ->from(Cars::class, 'c');

        if ($search) {
            $qb->andWhere('LOWER(c.model) LIKE :search OR LOWER(c.location) LIKE :search')
               ->setParameter('search', '%' . strtolower($search) . '%');
        }

        if ($seats) {
            $qb->andWhere('c.seats = :seats')
               ->setParameter('seats', (int)$seats);
        }

        if ($priceRange) {
            if ($priceRange === '0-50') {
                $qb->andWhere('c.price_per_day <= 50');
            } elseif ($priceRange === '50-100') {
                $qb->andWhere('c.price_per_day > 50 AND c.price_per_day <= 100');
            } elseif ($priceRange === '100-200') {
                $qb->andWhere('c.price_per_day > 100 AND c.price_per_day <= 200');
            } elseif ($priceRange === '200+') {
                $qb->andWhere('c.price_per_day > 200');
            }
        }

        $cars = $qb->getQuery()->getResult();
        
        if ($request->isXmlHttpRequest()) {
            return $this->render('cars/_cars_list.html.twig', [
                'cars' => $cars
            ]);
        }
        
        return $this->render('cars/list.html.twig', [
            'cars' => $cars,
            'mapbox_access_token' => $_ENV['MAPBOX_ACCESS_TOKEN'] ?? '',
            'search' => $search,
            'seats' => $seats,
            'price' => $priceRange
        ]);
    }

    #[Route('/manage', name: 'cars_manage')]
    public function manage(): Response
    {
        $cars = $this->entityManager->getRepository(Cars::class)->findAll();
        return $this->render('cars/manage.html.twig', [
            'cars' => $cars
        ]);
    }

    #[Route('/new', name: 'cars_new')]
    public function new(Request $request): Response
    {
        $car = new Cars();
        $form = $this->createForm(CarType::class, $car);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Get coordinates using Mapbox
                $coordinates = $this->mapboxService->getCoordinates($car->getLocation());
                if ($coordinates) {
                    $car->setLatitude($coordinates['latitude']);
                    $car->setLongitude($coordinates['longitude']);
                }

                $this->entityManager->persist($car);
                $this->entityManager->flush();

                $this->addFlash('success', 'La voiture a été ajoutée avec succès.');
                return $this->redirectToRoute('cars_list');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'enregistrement : ' . $e->getMessage());
            }
        }

        return $this->render('cars/new.html.twig', [
            'form' => $form->createView(),
            'car' => $car,
            'mapbox_access_token' => $_ENV['MAPBOX_ACCESS_TOKEN'] ?? ''
        ]);
    }

    #[Route('/{id}/edit', name: 'cars_edit')]
    public function edit(Request $request, int $id): Response
    {
        $car = $this->entityManager->getRepository(Cars::class)->find($id);

        if (!$car) {
            throw $this->createNotFoundException('La voiture demandée n\'existe pas.');
        }

        $form = $this->createForm(CarType::class, $car);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Get coordinates using Mapbox
                $coordinates = $this->mapboxService->getCoordinates($car->getLocation());
                if ($coordinates) {
                    $car->setLatitude($coordinates['latitude']);
                    $car->setLongitude($coordinates['longitude']);
                }

                $this->entityManager->flush();

                $this->addFlash('success', 'La voiture a été modifiée avec succès.');
                return $this->redirectToRoute('cars_manage');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur : ' . $e->getMessage());
            }
        }

        return $this->render('cars/edit.html.twig', [
            'form' => $form->createView(),
            'car' => $car,
            'mapbox_access_token' => $_ENV['MAPBOX_ACCESS_TOKEN'] ?? ''
        ]);
    }

    #[Route('/{id}/delete', name: 'cars_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, int $id): Response
    {
        try {
            $car = $this->entityManager->getRepository(Cars::class)->find($id);

            if (!$car) {
                throw $this->createNotFoundException('La voiture demandée n\'existe pas.');
            }

            if (!$this->isCsrfTokenValid('delete'.$car->getId(), $request->request->get('_token'))) {
                throw new \Exception('Token CSRF invalide.');
            }

            // Supprimer d'abord toutes les réservations associées
            foreach ($car->getReservations() as $reservation) {
                $this->entityManager->remove($reservation);
            }

            // Puis supprimer la voiture
            $this->entityManager->remove($car);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'La voiture et ses réservations ont été supprimées avec succès.');
            
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('cars_manage');
    }

    #[Route('/{id}/api/delete', name: 'cars_api_delete', methods: ['DELETE'])]
    public function deleteCar(int $id): JsonResponse
    {
        $car = $this->entityManager->getRepository(Cars::class)->find($id);

        if (!$car) {
            return new JsonResponse(['error' => 'Car not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($car);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Car deleted successfully'
        ]);
    }

    #[Route('/{id}', name: 'cars_show', methods: ['GET'])]
    public function show(Cars $car): Response
    {
        return $this->render('cars/show.html.twig', [
            'car' => $car,
            'mapbox_access_token' => $_ENV['MAPBOX_ACCESS_TOKEN'] ?? ''
        ]);
    }
}