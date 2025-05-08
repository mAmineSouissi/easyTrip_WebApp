<?php

namespace App\Controller;

use App\Entity\Cars;
use App\Entity\User;
use App\Entity\Res_transport;
use App\Form\ResTransportType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Service\NotificationService;

#[Route('/res_transport')]
class ResTransportController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private NotificationService $notificationService;

    public function __construct(EntityManagerInterface $entityManager, NotificationService $notificationService)
    {
        $this->entityManager = $entityManager;
        $this->notificationService = $notificationService;
    }

    #[Route('/', name: 'reservations_list', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new \Exception('Utilisateur non connecté.');
        }

        $reservations = $this->entityManager->getRepository(Res_transport::class)
            ->findBy(['user' => $user], ['startDate' => 'DESC']);

        return $this->render('res_transport/index.html.twig', [
            'res_transports' => $reservations,
            'mapbox_access_token' => $_ENV['MAPBOX_ACCESS_TOKEN'] ?? ''
        ]);
    }

    #[Route('/manage', name: 'reservations_manage')]
    public function manage(): Response
    {
        $reservations = $this->entityManager->getRepository(Res_transport::class)->findAll();
        return $this->render('res_transport/manage.html.twig', [
            'reservations' => $reservations
        ]);
    }

    #[Route('/new', name: 'reservations_new')]
    public function new(Request $request): Response
    {
        $car_id = $request->query->get('car_id');
        $selectedCar = null;

        if ($car_id) {
            $selectedCar = $this->entityManager->getRepository(Cars::class)->find($car_id);
            if (!$selectedCar) {
                $this->addFlash('danger', 'Erreur : La voiture demandée n\'existe pas.');
                return $this->redirectToRoute('cars_list');
            }
        }

        $reservation = new Res_transport();
        $reservation->setStatus('En attente');

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new \Exception('Utilisateur non connecté.');
        }
        $reservation->setUser($user);

        if ($selectedCar) {
            $reservation->setCar($selectedCar);
            $reservation->setLatitude($selectedCar->getLatitude());
            $reservation->setLongitude($selectedCar->getLongitude());
            $reservation->setTotalPrice($selectedCar->getPricePerDay());
        }

        $form = $this->createForm(ResTransportType::class, $reservation, [
            'selected_car' => $selectedCar,
            'is_admin' => $this->isGranted('ROLE_ADMIN')
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $car = $reservation->getCar();
                $startDate = $reservation->getStartDate();
                $endDate = $reservation->getEndDate();
                $totalPrice = $reservation->getTotalPrice();

                $days = $endDate->diff($startDate)->days + 1;
                $calculatedPrice = $days * $car->getPricePerDay();

                if (abs($calculatedPrice - $totalPrice) > 0.01) {
                    throw new \Exception('Le prix total ne correspond pas au prix calculé.');
                }

                if ($totalPrice <= 0) {
                    throw new \Exception('Erreur dans le calcul du prix. Veuillez réessayer.');
                }

                $reservation->setStatus('Confirmée');

                if (!$car->isAvailableForDates($startDate, $endDate)) {
                    throw new \Exception('Cette voiture n\'est pas disponible pour les dates sélectionnées.');
                }

                $this->entityManager->persist($reservation);
                $this->entityManager->flush();

                $this->notificationService->sendReservationConfirmation($user, $reservation);

                $this->addFlash('success', sprintf(
                    'Réservation confirmée avec succès! Voiture: %s, du %s au %s. Prix total: %.2f€',
                    $car->getModel(),
                    $startDate->format('d/m/Y'),
                    $endDate->format('d/m/Y'),
                    $totalPrice
                ));

                return $this->redirectToRoute('reservations_list');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            }
        }

        return $this->render('res_transport/new.html.twig', [
            'form' => $form->createView(),
            'car' => $selectedCar,
            'mapbox_access_token' => $this->getParameter('mapbox_access_token'),
            'reservations' => []
        ]);
    }

    #[Route('/{id}/edit', name: 'reservations_edit')]
    public function edit(Request $request, int $id): Response
    {
        $form = null;
        $reservation = null;
        $originalCar = null;

        try {
            $reservation = $this->entityManager->getRepository(Res_transport::class)->find($id);

            if (!$reservation) {
                throw $this->createNotFoundException('La réservation demandée n\'existe pas.');
            }

            $originalCar = $reservation->getCar();

            if (!$originalCar) {
                throw new \Exception('Aucune voiture n\'est associée à cette réservation.');
            }

            $form = $this->createForm(ResTransportType::class, $reservation, [
                'selected_car' => $originalCar,
                'is_admin' => false
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $newCar = $reservation->getCar();
                $startDate = $reservation->getStartDate();
                $endDate = $reservation->getEndDate();

                if (!$newCar->isAvailableForDates($startDate, $endDate, $reservation)) {
                    throw new \Exception('Cette voiture n\'est pas disponible pour les dates sélectionnées.');
                }

                $days = $endDate->diff($startDate)->days + 1;
                $calculatedPrice = $days * $newCar->getPricePerDay();
                $reservation->setTotalPrice($calculatedPrice);

                $this->entityManager->flush();

                $this->addFlash('success', sprintf(
                    'Réservation modifiée avec succès! Voiture: %s, du %s au %s. Prix total: %.2f€',
                    $newCar->getModel(),
                    $startDate->format('d/m/Y'),
                    $endDate->format('d/m/Y'),
                    $calculatedPrice
                ));

                return $this->redirectToRoute('reservations_list');
            }
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->render('res_transport/edit.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation,
            'car' => $originalCar,
            'mapbox_access_token' => $this->getParameter('mapbox_access_token'),
            'editMode' => true
        ]);
    }

    #[Route('/{id}/admin-edit', name: 'reservations_admin_edit')]
    public function adminEdit(Request $request, int $id): Response
    {
        $form = null;
        $reservation = null;
        $originalCar = null;

        try {
            $reservation = $this->entityManager->getRepository(Res_transport::class)->find($id);

            if (!$reservation) {
                throw $this->createNotFoundException('La réservation demandée n\'existe pas.');
            }

            $originalCar = $reservation->getCar();

            if (!$originalCar) {
                throw new \Exception('Aucune voiture n\'est associée à cette réservation.');
            }

            $form = $this->createForm(ResTransportType::class, $reservation, [
                'selected_car' => $originalCar,
                'is_admin' => true
            ]);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $newCar = $reservation->getCar();
                $startDate = $reservation->getStartDate();
                $endDate = $reservation->getEndDate();
                $newStatus = $form->get('status')->getData();

                $days = $endDate->diff($startDate)->days + 1;
                $calculatedPrice = $days * $newCar->getPricePerDay();
                $reservation->setTotalPrice($calculatedPrice);

                $reservation->setStatus($newStatus);

                $this->entityManager->flush();

                if ($reservation->getStatus() === 'Confirmée') {
                    $adminEmail = $form->get('admin_email')->getData();
                    if ($adminEmail) {
                        $user = $reservation->getUser();
                        $userClone = clone $user;
                        $userClone->setEmail($adminEmail);
                        $this->notificationService->sendReservationConfirmation($userClone, $reservation);
                    } else {
                        $this->notificationService->sendReservationConfirmation($reservation->getUser(), $reservation);
                    }
                }

                $this->addFlash('success', sprintf(
                    'Réservation modifiée avec succès! Voiture: %s, du %s au %s. Statut: %s. Prix total: %.2f€',
                    $newCar->getModel(),
                    $startDate->format('d/m/Y'),
                    $endDate->format('d/m/Y'),
                    $newStatus,
                    $calculatedPrice
                ));

                return $this->redirectToRoute('reservations_manage');
            }
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->render('res_transport/admin_edit.html.twig', [
            'form' => $form->createView(),
            'reservation' => $reservation,
            'car' => $originalCar,
            'mapbox_access_token' => $this->getParameter('mapbox_access_token')
        ]);
    }

    #[Route('/{id}/delete', name: 'reservations_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, int $id): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new \Exception('Utilisateur non connecté.');
        }

        $reservation = $this->entityManager->getRepository(Res_transport::class)->find($id);

        if (!$reservation) {
            throw $this->createNotFoundException('La réservation demandée n\'existe pas.');
        }

        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($reservation);
            $this->entityManager->flush();
            $this->addFlash('success', 'La réservation a été supprimée avec succès.');
        }

        return $this->redirectToRoute('reservations_list');
    }
}
