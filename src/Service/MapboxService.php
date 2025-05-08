<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class MapboxService
{
    private string $accessToken;
    private string $baseUrl = 'https://api.mapbox.com';

    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Get coordinates for a given address
     *
     * @param string $address
     * @return array|null
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getCoordinates(string $address): ?array
    {
        $client = HttpClient::create();
        $url = sprintf(
            '%s/geocoding/v5/mapbox.places/%s.json?access_token=%s',
            $this->baseUrl,
            urlencode($address),
            $this->accessToken
        );

        try {
            $response = $client->request('GET', $url);
            $data = $response->toArray();

            if (isset($data['features'][0]['center'])) {
                return [
                    'longitude' => $data['features'][0]['center'][0],
                    'latitude' => $data['features'][0]['center'][1]
                ];
            }
        } catch (\Exception $e) {
            // Log the error if needed
            return null;
        }

        return null;
    }

    /**
     * Get address for given coordinates
     *
     * @param float $longitude
     * @param float $latitude
     * @return string|null
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getAddress(float $longitude, float $latitude): ?string
    {
        $client = HttpClient::create();
        $url = sprintf(
            '%s/geocoding/v5/mapbox.places/%f,%f.json?access_token=%s',
            $this->baseUrl,
            $longitude,
            $latitude,
            $this->accessToken
        );

        try {
            $response = $client->request('GET', $url);
            $data = $response->toArray();

            if (isset($data['features'][0]['place_name'])) {
                return $data['features'][0]['place_name'];
            }
        } catch (\Exception $e) {
            // Log the error if needed
            return null;
        }

        return null;
    }
} 