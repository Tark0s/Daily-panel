<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WeatherController extends AbstractController
{
    #[Route('/weather-today', name: 'weather-today')]
    public function index(WeatherService $weatherService): Response
    {
        $weatherData = $weatherService->getWeather();
        $airPollutionData = $weatherService->getAirPollution();
        $temperatureForecastData = $weatherService->getWeatherForecastTemperatures();

        return $this->render('weather/index.html.twig', [
            'weatherData' => $weatherData,
            'airPollutionData' => $airPollutionData,
            'temperatureForecastData' => $temperatureForecastData,
        ]);
    }
}