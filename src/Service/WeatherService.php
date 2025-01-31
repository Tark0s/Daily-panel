<?php

namespace App\Service;

use App\ValueObject\Coordinates;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private CoordinatesService $coordinatesService;
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(CoordinatesService $coordinatesService, HttpClientInterface $client, string $apiKey)
    {
        $this->coordinatesService = $coordinatesService;
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function getWeather(string $city = 'Lublin'): array
    {
        $coordinates = $this->coordinatesService->getCoordinates($city);

        try {
            $url = $this->buildWeatherApiUrl($coordinates);

            $response = $this->client->request('GET', $url);

            return $response->toArray();
        } catch (TransportExceptionInterface $e) {
            throw new \Exception('Error while fetching weather data: ' . $e->getMessage());
        }
    }

    private function buildWeatherApiUrl(Coordinates $coordinates)
    {
        $queryParams = [
            'lat' => $coordinates->getLatitude(),
            'lon' => $coordinates->getLongitude(),
            'appid' => $this->apiKey,
            'units' => 'metric'
        ];

        return 'https://api.openweathermap.org/data/2.5/weather?' . http_build_query($queryParams);
    }

}