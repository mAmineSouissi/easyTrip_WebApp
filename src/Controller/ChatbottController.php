<?php

namespace App\Controller;

use App\Entity\Cars;
use App\Service\GeminiService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/chatbot')]
class ChatbottController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private GeminiService $geminiService;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        GeminiService $geminiService,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->geminiService = $geminiService;
        $this->logger = $logger;
    }

    #[Route('/', name: 'chatbot_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('chatbott/index.html.twig');
    }

    #[Route('/chat', name: 'chatbot_conversation', methods: ['POST'])]
    public function chat(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $userMessage = $data['message'] ?? '';

            if (empty($userMessage)) {
                return new JsonResponse([
                    'message' => 'Veuillez entrer un message.',
                    'status' => 'error'
                ], 400);
            }

            $this->logger->info('Processing general chat request', [
                'userMessage' => $userMessage
            ]);

            // Generate AI response for general conversation
            $response = $this->geminiService->generateResponse($userMessage);

            $this->logger->info('Generated response', [
                'response' => $response
            ]);

            return new JsonResponse([
                'message' => $response,
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error in ChatbotController chat', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return new JsonResponse([
                'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.',
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/suggest-car', name: 'chatbot_suggest_car', methods: ['POST'])]
    public function suggestCar(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $userMessage = $data['message'] ?? '';

            if (empty($userMessage)) {
                return new JsonResponse([
                    'message' => 'Veuillez entrer un message.',
                    'status' => 'error'
                ], 400);
            }

            $this->logger->info('Processing chatbot request', [
                'userMessage' => $userMessage
            ]);

            // Get available cars for context
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('c')
                ->from(Cars::class, 'c')
                ->where('c.available = true');

            // Add basic filters if provided
            if (isset($data['location'])) {
                $qb->andWhere('LOWER(c.location) LIKE :location')
                   ->setParameter('location', '%' . strtolower($data['location']) . '%');
            }
            if (isset($data['seats'])) {
                $qb->andWhere('c.seats = :seats')
                   ->setParameter('seats', (int)$data['seats']);
            }
            if (isset($data['price_range'])) {
                $priceRange = explode('-', $data['price_range']);
                if (count($priceRange) === 2) {
                    $qb->andWhere('c.price_per_day BETWEEN :minPrice AND :maxPrice')
                       ->setParameter('minPrice', (float)$priceRange[0])
                       ->setParameter('maxPrice', (float)$priceRange[1]);
                }
            }

            $cars = $qb->getQuery()->getResult();

            // Prepare cars data for context
            $carsContext = array_map(function(Cars $car) {
                return [
                    'model' => $car->getModel(),
                    'seats' => $car->getSeats(),
                    'location' => $car->getLocation(),
                    'price_per_day' => $car->getPricePerDay(),
                    'available' => $car->getReservations()->isEmpty(),
                    'description' => $car->getDescription() ?? '',
                    'features' => $car->getFeatures() ?? []
                ];
            }, $cars);

            $this->logger->info('Found cars for context', [
                'count' => count($carsContext),
                'context' => $carsContext
            ]);

            // Prepare the prompt for Gemini
            $prompt = "Tu es un assistant de location de voitures pour EasyTrip. Ton rôle est d'aider les utilisateurs à trouver la voiture idéale pour leurs besoins. Sois amical, professionnel et concis. Communique toujours en français.\n\n";

            if (!empty($carsContext)) {
                $prompt .= "Voici les voitures disponibles :\n";
                foreach ($carsContext as $car) {
                    $prompt .= sprintf(
                        "- %s (%d places) à %s pour %d€/jour. %s\n",
                        $car['model'],
                        $car['seats'],
                        $car['location'],
                        $car['price_per_day'],
                        $car['description']
                    );
                }
            } else {
                $prompt .= "Aucune voiture n'est disponible pour le moment.\n";
            }

            $prompt .= "\nQuestion de l'utilisateur : " . $userMessage;

            // Generate AI response
            $response = $this->geminiService->generateResponse($prompt);

            $this->logger->info('Generated response', [
                'response' => $response
            ]);

            return new JsonResponse([
                'message' => $response,
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error in ChatbotController', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return new JsonResponse([
                'message' => 'Une erreur est survenue. Veuillez réessayer plus tard.',
                'status' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 