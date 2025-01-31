<?php

namespace App\Service;

use App\ValueObject\Coordinates;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CoordinatesService
{
    private HttpClientInterface $client;
    private string $apiKey;
    private string $city;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function getCoordinates(string $city, int $limit = 5): Coordinates
    {
        try {
            $response = $this->client->request(
                'GET',
                "http://api.openweathermap.org/geo/1.0/direct?q={$city}&limit={$limit}&appid=$this->apiKey"
            );
        } catch (TransportExceptionInterface $e) {
            throw new \Exception('Error while fetching weather data: ' . $e->getMessage());
        }

        $coordinates = $response->toArray();
        return new Coordinates($coordinates[0]['lat'], $coordinates[0]['lon']);
    }
}