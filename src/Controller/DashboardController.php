<?php

declare(strict_types=1);

namespace App\Controller;

use App\ApiResource\Measure;
use App\Repository\CityRepository;
use App\State\FakeMeasureTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final class DashboardController extends AbstractController
{
    use FakeMeasureTrait;

    public function __construct(
        private readonly CityRepository $cityRepository,
        private readonly ChartBuilderInterface $chartBuilder
    ) {
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig');
    }

    #[Route('/dashboard/{id}', name: 'app_dashboard_city')]
    public function city(string $id): Response
    {
        $city = $this->cityRepository->findOneBy([
            'id' => $id,
        ]);
        $city !== null || throw $this->createNotFoundException();

        $measures = $this->getMeasures($city);
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setOptions([
            'responsive' => true,
            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],
            'stacked' => false,
        ]);

        $chart->setData([
            'labels' => array_map(static fn (Measure $measure) => $measure->date->format('Y-m-d'), $measures),
            'scales' => [
                'y' => [
                    'type' => 'line',
                    'display' => true,
                    'position' => 'left',
                ],
                'y1' => [
                    'type' => 'line',
                    'display' => true,
                    'position' => 'right',
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
            'datasets' => [
                [
                    'label' => 'Temperature',
                    'yAxisID' => 'y',
                    'backgroundColor' => '#f87979',
                    'borderColor' => '#f87979',
                    'lineTension' => 0.4,
                    'data' => array_map(static fn (Measure $measure) => $measure->temperature, $measures),
                ],
                [
                    'label' => 'Wind speed',
                    'yAxisID' => 'y1',
                    'backgroundColor' => '#905ed1',
                    'borderColor' => '#905ed1',
                    'lineTension' => 0.4,
                    'data' => array_map(static fn (Measure $measure) => $measure->windSpeed, $measures),
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 100,
                ],
            ],
        ]);

        return $this->render('dashboard/city.html.twig', [
            'city' => $city,
            'chart' => $chart,
        ]);
    }
}
