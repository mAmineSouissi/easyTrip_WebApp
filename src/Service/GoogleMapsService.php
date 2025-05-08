<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleMapsService
{
    private $httpClient;
    private $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    public function getCoordinates(string $address): ?array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://maps.googleapis.com/maps/api/geocode/json', [
                'query' => [
                    'address' => $address,
                    'key' => $this->apiKey
                ]
            ]);

            $data = json_decode($response->getContent(), true);

            if ($data['status'] === 'OK' && !empty($data['results'])) {
                $location = $data['results'][0]['geometry']['location'];
                return [
                    'latitude' => $location['lat'],
                    'longitude' => $location['lng']
                ];
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
} 