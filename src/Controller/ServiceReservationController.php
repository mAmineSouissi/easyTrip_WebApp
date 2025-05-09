<?php

namespace App\Controller;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\ServiceReservation;
use App\Repository\ServiceReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ServiceReservationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;


class ServiceReservationController extends AbstractController
{
    #[Route('/servicereservations', name: 'app_servicereservations')]
public function show(ServiceReservationRepository $servicereservationRepository, Request $request, PaginatorInterface $paginator): Response
{
    $service = $request->query->get('service');
    $queryBuilder = $servicereservationRepository->createQueryBuilder('r');
    if ($service) {
        $queryBuilder
            ->where('r.service LIKE :service')
            ->setParameter('service', '%' . $service . '%');
    }
    $pagination = $paginator->paginate(
        $queryBuilder->getQuery(), 
        $request->query->getInt('page', 1), 
        4
    );
    return $this->render('servicereservation/show.html.twig', [
        'pagination' => $pagination,
    ]);
}


  #[Route('/servicereservation/ajouter', name: 'app_servicereservation_add')]
  public function add(Request $request, EntityManagerInterface $em): Response
  {
      $servicereservation = new ServiceReservation(); 
      $form = $this->createForm(ServiceReservationType::class, $servicereservation);
      $form->handleRequest($request);
  
      if ($form->isSubmitted() && $form->isValid()) {
          $em->persist($servicereservation);
          $em->flush();
  
          $this->addFlash('success', 'Service Réservation ajoutée avec succès !');
          return $this->redirectToRoute('app_servicereservations');
      }
  
      return $this->render('servicereservation/add.html.twig', [
          'form' => $form->createView(),
      ]);
  }

  #[Route('/servicereservation/supprimer/{id_servicereservation}', name: 'app_servicereservation_delete', methods: ['POST'])]
  public function delete(ServiceReservation $servicereservation, EntityManagerInterface $em, Request $request): Response
   {
  if ($this->isCsrfTokenValid('delete'.$servicereservation->getIdServicereservation(), $request->request->get('_token'))) {
      $em->remove($servicereservation);
      $em->flush();
      $this->addFlash('success', 'Service Réservation supprimée avec succès.');
  }

  return $this->redirectToRoute('app_servicereservations');
   }
  
   #[Route('/servicereservation/modifier/{id_servicereservation}', name: 'app_servicereservation_edit')]
   public function edit(Request $request, ServiceReservation $servicereservation, EntityManagerInterface $em): Response
   {
       $form = $this->createForm(ServiceReservationType::class, $servicereservation);
       $form->handleRequest($request);
   
       if ($form->isSubmitted() && $form->isValid()) {
           $em->flush();
           $this->addFlash('success', 'Service Réservation modifiée avec succès.');
           return $this->redirectToRoute('app_servicereservations');
       }
   
       return $this->render('servicereservation/edit.html.twig', [
           'form' => $form->createView(),
           'servicereservation' => $servicereservation,
       ]);
   }

    
}