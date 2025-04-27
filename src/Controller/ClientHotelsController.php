<?php

namespace App\Controller;

use App\Entity\Hotels;
use App\Form\HotelSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/Chotels')]
class ClientHotelsController extends AbstractController
{
    private $httpClient;
    private $apiKey = '4f42e896858ac41e68bfa889b1082219';

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/', name: 'app_client_hotels_index', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HotelSearchType::class);
        $form->handleRequest($request);

        $queryBuilder = $entityManager->getRepository(Hotels::class)
            ->createQueryBuilder('h')
            ->leftJoin('h.promotion', 'p')
            ->addSelect('p');

        if ($request->isXmlHttpRequest()) {
            return $this->handleAjaxRequest($request, $queryBuilder);
        }

        $this->applyFilters($form, $queryBuilder);
        $hotels = $queryBuilder->getQuery()->getResult();

        $exchangeRates = $this->getExchangeRates();

        return $this->render('client_hotels/index.html.twig', [
            'hotels' => $hotels,
            'form' => $form->createView(),
            'exchangeRates' => $exchangeRates,
        ]);
    }

    #[Route('/{id_hotel}', name: 'app_client_hotels_show', methods: ['GET'])]
    public function show(int $id_hotel, EntityManagerInterface $entityManager): Response
    {
        $hotel = $entityManager->getRepository(Hotels::class)->find($id_hotel);

        if (!$hotel) {
            throw $this->createNotFoundException('Hôtel non trouvé');
        }

        $exchangeRates = $this->getExchangeRates();

        return $this->render('client_hotels/show.html.twig', [
            'hotel' => $hotel,
            'exchangeRates' => $exchangeRates,
        ]);
    }

    private function getExchangeRates(): array
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                "http://api.exchangeratesapi.io/v1/latest?access_key={$this->apiKey}&base=EUR&symbols=USD,GBP,TND,CAD"
            );

            $data = $response->toArray();
            return [
                'USD' => $data['rates']['USD'] ?? 1.18,
                'GBP' => $data['rates']['GBP'] ?? 0.85,
                'TND' => $data['rates']['TND'] ?? 3.30, // Fallback rate for TND
                'CAD' => $data['rates']['CAD'] ?? 1.47, // Fallback rate for CAD
                'lastUpdate' => date('Y-m-d H:i:s', $data['timestamp'] ?? time())
            ];
        } catch (\Exception $e) {
            return [
                'USD' => 1.18,
                'GBP' => 0.85,
                'TND' => 3.30,
                'CAD' => 1.47,
                'lastUpdate' => date('Y-m-d H:i:s')
            ];
        }
    }

    private function handleAjaxRequest(Request $request, $queryBuilder): JsonResponse
    {
        $data = $request->request->all();
        $searchTerm = $data['search'] ?? null;
        $sortBy = $data['sort'] ?? 'default';
        $currency = $data['currency'] ?? 'EUR';
        $filters = $data['filters'] ?? [];

        if ($searchTerm) {
            $queryBuilder->andWhere('h.name LIKE :search OR h.city LIKE :search OR h.description LIKE :search')
                ->setParameter('search', '%'.$searchTerm.'%');
        }

        foreach ($filters as $filter => $value) {
            if ($value) {
                switch ($filter) {
                    case 'rating':
                        $queryBuilder->andWhere('h.rating >= :rating')
                            ->setParameter('rating', $value);
                        break;
                    case 'maxPrice':
                        $queryBuilder->andWhere('h.price <= :maxPrice')
                            ->setParameter('maxPrice', $value);
                        break;
                    case 'hasPromo':
                        $queryBuilder->andWhere('p.id IS NOT NULL');
                        break;
                }
            }
        }

        $this->applySorting($sortBy, $queryBuilder);

        $hotels = $queryBuilder->getQuery()->getResult();
        $hotelsArray = $this->convertHotelsToArray($hotels);
        $convertedHotels = $this->convertPrices($hotelsArray, $currency);
        
        $html = $this->renderView('client_hotels/_hotels_list.html.twig', [
            'hotels' => $convertedHotels,
            'currency' => $currency
        ]);

        return new JsonResponse(['html' => $html]);
    }

    private function convertHotelsToArray(array $hotels): array
    {
        return array_map(function($hotel) {
            return [
                'id' => $hotel->getIdHotel(),
                'id_hotel' => $hotel->getIdHotel(),
                'name' => $hotel->getName(),
                'city' => $hotel->getCity(),
                'description' => $hotel->getDescription(),
                'rating' => $hotel->getRating(),
                'price' => $hotel->getPrice(),
                'originalPrice' => $hotel->getPromotion() ? $hotel->getOriginalPrice() : $hotel->getPrice(),
                'promotion' => $hotel->getPromotion() ? [
                    'discountPercentage' => $hotel->getPromotion()->getDiscountPercentage(),
                    'validUntil' => $hotel->getPromotion()->getValidUntil()
                ] : null,
                'image' => $hotel->getImage()
            ];
        }, $hotels);
    }

    private function convertPrices(array $hotels, string $currency): array
    {
        $rates = $this->getExchangeRates();
        
        foreach ($hotels as &$hotel) {
            $price = $hotel['price'];
            $originalPrice = $hotel['originalPrice'] ?? $price;
            
            switch($currency) {
                case 'USD':
                    $hotel['price'] = $price * $rates['USD'];
                    $hotel['originalPrice'] = $originalPrice * $rates['USD'];
                    break;
                case 'GBP':
                    $hotel['price'] = $price * $rates['GBP'];
                    $hotel['originalPrice'] = $originalPrice * $rates['GBP'];
                    break;
                case 'TND':
                    $hotel['price'] = $price * $rates['TND'];
                    $hotel['originalPrice'] = $originalPrice * $rates['TND'];
                    break;
                case 'CAD':
                    $hotel['price'] = $price * $rates['CAD'];
                    $hotel['originalPrice'] = $originalPrice * $rates['CAD'];
                    break;
                default:
                    break;
            }
        }
        
        return $hotels;
    }

    private function applySorting(string $sortBy, $queryBuilder): void
    {
        switch ($sortBy) {
            case 'promo_desc':
                $queryBuilder->orderBy('p.discountPercentage', 'DESC');
                break;
            case 'promo_asc':
                $queryBuilder->orderBy('p.discountPercentage', 'ASC');
                break;
            case 'price_desc':
                $queryBuilder->orderBy('h.price', 'DESC');
                break;
            case 'price_asc':
                $queryBuilder->orderBy('h.price', 'ASC');
                break;
            case 'rating_desc':
                $queryBuilder->orderBy('h.rating', 'DESC');
                break;
            case 'rating_asc':
                $queryBuilder->orderBy('h.rating', 'ASC');
                break;
            default:
                $queryBuilder->addOrderBy('CASE WHEN p.id IS NOT NULL THEN 0 ELSE 1 END', 'ASC')
                    ->addOrderBy('h.id_hotel', 'ASC');
                break;
        }
    }

    private function applyFilters($form, $queryBuilder): void
    {
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

            if (!empty($data['sortBy'])) {
                $this->applySorting($data['sortBy'], $queryBuilder);
            }
        }
    }
}