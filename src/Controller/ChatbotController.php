<?php

namespace App\Controller;

use App\Service\ChatbotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatbotController extends AbstractController
{
    #[Route('/chatbot', name: 'chatbot_interface')]
    public function index(): Response
    {
        return $this->render('chatbot/index.html.twig');
    }

    #[Route('/api/gemini/message', name: 'chatbot_message', methods: ['POST'])]
    public function processMessage(Request $request, ChatbotService $chatbotService): JsonResponse
    {
        // Vérifier si c'est une requête AJAX
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse([
                'type' => 'text',
                'content' => 'Cette route ne peut être appelée que via AJAX'
            ], 400);
        }

        try {
            $data = json_decode($request->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return new JsonResponse([
                    'type' => 'text',
                    'content' => 'Données JSON invalides'
                ], 400);
            }

            $message = $data['message'] ?? '';

            if (empty($message)) {
                return new JsonResponse([
                    'type' => 'text',
                    'content' => 'Le message ne peut pas être vide'
                ], 400);
            }

            $response = $chatbotService->processMessage($message);
            
            // S'assurer que la réponse est toujours un tableau
            if (!is_array($response)) {
                $response = [
                    'type' => 'text',
                    'content' => 'Une erreur est survenue lors du traitement de votre demande.'
                ];
            }

            $jsonResponse = new JsonResponse($response);
            $jsonResponse->headers->set('Content-Type', 'application/json');
            $jsonResponse->headers->set('Access-Control-Allow-Origin', '*');
            $jsonResponse->headers->set('Access-Control-Allow-Methods', 'POST');
            $jsonResponse->headers->set('Access-Control-Allow-Headers', 'Content-Type');

            return $jsonResponse;
        } catch (\Exception $e) {
            return new JsonResponse([
                'type' => 'text',
                'content' => 'Une erreur est survenue : ' . $e->getMessage()
            ], 500);
        }
    }
} 