<?php

namespace App\Controller;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class ReservationController extends AbstractController
{
    #[Route('/reservations', name: 'app_reservations')]
public function show(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
{
    $nom = $request->query->get('nom');
    $prenom = $request->query->get('prenom');
    $orderDate = $request->query->get('orderDate');

    //trier par nom
    $sort = $request->query->get('sort', 'r.nom'); 
    $direction = $request->query->get('direction', 'asc'); 
    $allowedSorts = ['r.nom'];
    if (!in_array($sort, $allowedSorts)) {
        $sort = 'r.nom';
    }

    $queryBuilder = $reservationRepository->createQueryBuilder('r');

    if ($nom) {
        $queryBuilder->andWhere('r.nom LIKE :nom')
                     ->setParameter('nom', '%' . $nom . '%');
    }

    if ($prenom) {
        $queryBuilder->andWhere('r.prenom LIKE :prenom')
                     ->setParameter('prenom', '%' . $prenom . '%');
    }

    if ($orderDate) {
        $queryBuilder->andWhere('r.orderDate = :orderDate')
                     ->setParameter('orderDate', $orderDate);
    }

    $queryBuilder->orderBy($sort, $direction);
        


    $pagination = $paginator->paginate(
        $queryBuilder->getQuery(),
        $request->query->getInt('page', 1),
        4
    );

    return $this->render('reservation/show.html.twig', [
        'pagination' => $pagination,
    ]);
}

  #[Route('/reservation/ajouter', name: 'app_reservation_add')]
  public function add(Request $request, EntityManagerInterface $em): Response
  {
      $reservation = new Reservation();
      $reservation->setOrderDate(new \DateTime()); 
      $form = $this->createForm(ReservationType::class, $reservation);
      $form->handleRequest($request);
  
      if ($form->isSubmitted() && $form->isValid()) {
          $em->persist($reservation);
          $em->flush();
  
          $this->addFlash('success', 'Réservation ajoutée avec succès !');
          return $this->redirectToRoute('app_reservations');
      }
  
      return $this->render('reservation/add.html.twig', [
          'form' => $form->createView(),
      ]);
  }

  #[Route('/reservation/supprimer/{id_reservation}', name: 'app_reservation_delete', methods: ['POST'])]
  public function delete(Reservation $reservation, EntityManagerInterface $em, Request $request): Response
   {
  if ($this->isCsrfTokenValid('delete'.$reservation->getId_reservation(), $request->request->get('_token'))) {
      $em->remove($reservation);
      $em->flush();
      $this->addFlash('success', 'Réservation supprimée avec succès.');
  }

  return $this->redirectToRoute('app_reservations');
   }
  
   #[Route('/reservation/modifier/{id_reservation}', name: 'app_reservation_edit')]
   public function edit(Request $request, Reservation $reservation, EntityManagerInterface $em): Response
   {
       $form = $this->createForm(ReservationType::class, $reservation);
       $form->handleRequest($request);
   
       if ($form->isSubmitted() && $form->isValid()) {
           $em->flush();
           $this->addFlash('success', 'Réservation modifiée avec succès.');
           return $this->redirectToRoute('app_reservations');
       }
   
       return $this->render('reservation/edit.html.twig', [
           'form' => $form->createView(),
           'reservation' => $reservation,
       ]);
   }



   #[Route('/adminreservations', name: 'app_adminreservations')]
public function adminshow(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
{
    $reservations = $reservationRepository->findAll();
    return $this->render('reservation/admin_show.html.twig', [
        'reservations' => $reservations
    ]);
}


#[Route('/admin/statistiques/reservations', name: 'admin_statistiques_reservations')]
public function statistiques(ReservationRepository $reservationRepository): Response
{
    $stats = $reservationRepository->countReservationsByDate();
    $labels = [];
    $totals = [];

    foreach ($stats as $row) {
        $labels[] = $row['date']->format('Y-m-d'); // ou direct $row['date'] si string
        $totals[] = $row['total'];
    }

    return $this->render('statistique/index.html.twig', [
        'labels' => $labels,
        'totals' => $totals,
    ]);
}

#[Route('/facture', name: 'app_facture')]
public function facture(ReservationRepository $reservationRepository): Response
{
    $reservations = $reservationRepository->findAll();

    return $this->render('reservation/facture.html.twig', [
        'reservations' => $reservations,
    ]);
}


    
}