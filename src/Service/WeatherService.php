<?php

namespace App\Service;

use App\ValueObject\Coordinates;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private CoordinatesService $coordinatesService;
    private CacheInterface $cache;
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(
        CoordinatesService $coordinatesService,
        CacheInterface $cache,
        HttpClientInterface $client,
        string $apiKey
    ){
        $this->coordinatesService = $coordinatesService;
        $this->cache = $cache;
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function getWeather(string $city = 'Lublin'): array
    {
        return $this->cache->get('weather_' . strtolower($city), function (ItemInterface $item) use ($city) {
            $item->expiresAfter(1800);

            $coordinates = $this->coordinatesService->getCoordinates($city);

            try {
                $url = $this->buildWeatherApiUrl($coordinates);
                $response = $this->client->request('GET', $url)->toArray();
                $response['city'] = $city;

                return $response;
            } catch (TransportExceptionInterface $e) {
                throw new \Exception('Error while fetching weather data: ' . $e->getMessage());
            }
        });
    }

    public function getAirPollution(string $city = 'Lublin'): array
    {
        return $this->cache->get('air_pollution_' . strtolower($city), function (ItemInterface $item) use ($city) {
            $item->expiresAfter(1800);

            $coordinates = $this->coordinatesService->getCoordinates($city);

            try {
                $url = $this->buildAirPollutionApiUrl($coordinates);
                $response = $this->client->request('GET', $url)->toArray();
                $response['city'] = $city;

                return $response;
            } catch (TransportExceptionInterface $e) {
                throw new \Exception('Error while fetching air pollution data: ' . $e->getMessage());
            }
        });
    }

    public function getWeatherForecast(string $city): array
    {
        return $this->cache->get('weather_forecast_' . strtolower($city), function (ItemInterface $item) use ($city) {
            $item->expiresAfter(1800);

            $coordinates = $this->coordinatesService->getCoordinates($city);

            try {
                $url = $this->buildWeatherForecastApiUrl($coordinates);
                $response = $this->client->request('GET', $url)->toArray();
                $response['city'] = $city;

                return $response;
            } catch (TransportExceptionInterface $e) {
                throw new \Exception('Error while fetching weather forecast data: ' . $e->getMessage());
            }

        });
    }

    public function getWeatherForecastTemperatures(string $city = 'Lublin'): array
    {
        $forecast = $this->getWeatherForecast($city)['list'];

        $dailyTemperatures = [];

        foreach ($forecast as $data) {
            // Wyciągnięcie daty (ignorując godzinę)
            $date = explode(' ', $data['dt_txt'])[0];

            // Temperatury z "main"
            $temperature = $data['main']['temp'];

            // Grupowanie temperatur dla konkretnego dnia
            if (!isset($dailyTemperatures[$date])) {
                $dailyTemperatures[$date] = [
                    'date' => $date,
                    'min' => $temperature,
                    'max' => $temperature,
                ];
            } else {
                $dailyTemperatures[$date]['min'] = min($dailyTemperatures[$date]['min'], $temperature);
                $dailyTemperatures[$date]['max'] = max($dailyTemperatures[$date]['max'], $temperature);
            }
        }

        return $dailyTemperatures;
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

    private function buildAirPollutionApiUrl(Coordinates $coordinates)
    {
        $queryParams = [
            'lat' => $coordinates->getLatitude(),
            'lon' => $coordinates->getLongitude(),
            'appid' => $this->apiKey,
        ];

        return 'http://api.openweathermap.org/data/2.5/air_pollution?' . http_build_query($queryParams);
    }

    private function buildWeatherForecastApiUrl(Coordinates $coordinates)
    {
        $queryParams = [
            'lat' => $coordinates->getLatitude(),
            'lon' => $coordinates->getLongitude(),
            'appid' => $this->apiKey,
            'units' => 'metric'
        ];

        return 'http://api.openweathermap.org/data/2.5/forecast?' . http_build_query($queryParams);
    }

}