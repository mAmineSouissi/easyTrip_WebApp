<?php

namespace App\Controller;

use App\Entity\Hotels;
use App\Form\HotelSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/Chotels')]
class ClientHotelsController extends AbstractController
{
    #[Route('/', name: 'app_client_hotels_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HotelSearchType::class);
        $form->handleRequest($request);

        $queryBuilder = $entityManager
            ->getRepository(Hotels::class)
            ->createQueryBuilder('h');

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!empty($data['name'])) {
                $queryBuilder->andWhere('h.name LIKE :name')
                    ->setParameter('name', '%' . $data['name'] . '%');
            }

            if (!empty($data['city'])) {
                $queryBuilder->andWhere('h.city LIKE :city')
                    ->setParameter('city', '%' . $data['city'] . '%');
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
                    ->setParameter('typeRoom', '%' . $data['typeRoom'] . '%');
            }
        }

        $hotels = $queryBuilder->getQuery()->getResult();

        return $this->render('client_hotels/index.html.twig', [
            'hotels' => $hotels,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id_hotel}', name: 'app_client_hotels_show', methods: ['GET'])]
    public function show(int $id_hotel, EntityManagerInterface $entityManager): Response
    {
        $hotel = $entityManager->getRepository(Hotels::class)->find($id_hotel);

        if (!$hotel) {
            throw $this->createNotFoundException('Hôtel non trouvé');
        }

        return $this->render('client_hotels/show.html.twig', [
            'hotel' => $hotel,
        ]);
    }
}