<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    #[Route('/chatbot', name: 'chatbot')]
    public function index(): Response
    {
        return $this->render('reclamation/client/chat.html.twig');
    }

    #[Route('/chatbot/message', name: 'chatbot_message', methods: ['POST'])]
    public function handleMessage(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userMessage = $data['message'] ?? '';

        // Prompt formaté pour Zephyr
        $prompt = "<s>[INST] Tu es un assistant utile et bienveillant pour les clients EasyTrip. L'utilisateur a dit : \"$userMessage\". [/INST]";

        $client = HttpClient::create();
        try {
            $response = $client->request('POST', 'https://api-inference.huggingface.co/models/HuggingFaceH4/zephyr-7b-beta', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $_ENV['HUGGINGFACE_API_KEY'],
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'inputs' => $prompt,
                    'parameters' => [
                        'max_new_tokens' => 150,
                        'temperature' => 0.7,
                        'return_full_text' => false
                    ]
                ]
            ]);

            $responseData = $response->toArray();
            $reply = $responseData[0]['generated_text'] ?? "Je n'ai pas pu générer une réponse.";
        } catch (\Exception $e) {
            $reply = "❌ Erreur HuggingFace : " . $e->getMessage();
        }

        return new JsonResponse(['reply' => $reply]);
    }
}
