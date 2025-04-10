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


class ReservationController extends AbstractController
{
    #[Route('/reservations', name: 'app_reservations')]
    public function show(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
   {
    $nom = $request->query->get('nom');
    $queryBuilder = $reservationRepository->createQueryBuilder('r');
    if ($nom) {
        $queryBuilder
            ->where('r.nom LIKE :nom')
            ->setParameter('nom', '%' . $nom . '%');
    }
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
  

    
}