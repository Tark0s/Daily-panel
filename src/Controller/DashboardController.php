<?php

namespace App\Controller;

use App\Service\WeatherService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(WeatherService $weatherService): Response
    {
        $weatherData = $weatherService->getWeather();

        return $this->render('dashboard/index.html.twig', [
            'weather_data' => $weatherData,
        ]);
    }
}