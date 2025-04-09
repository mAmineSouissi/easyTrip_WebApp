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
        2
    );
    return $this->render('reservation/show.html.twig', [
        'pagination' => $pagination,
    ]);
  }

    
}