<?php
declare(strict_types=1);

namespace App\UX\Chart;

use App\Entity\ProductionUnit;
use App\Repository\ProductionValueRepository;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final readonly class ProductionChartFactory
{
    public function __construct(
        private ProductionValueRepository $productionValueRepository,
        private ChartBuilderInterface $chartBuilder,
    )
    {
    }


    public function create(ProductionUnit $productionUnit): Chart
    {
        $dataset = $this->productionValueRepository->findForUnitBetweenDates(
            $productionUnit,
            new \DateTimeImmutable('-1 day -2 hour'),
            new \DateTimeImmutable('now'),
        );

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels'   => array_map(fn (array $value): string => $value['startDate']->format('H:i'), $dataset),
            'datasets' => [
                [
                    'label' => 'Production',
                    'data'  => array_column($dataset, 'value'),
                    'borderColor' => 'blue',
                    'tension' => 0.4,
                ],
            ],
        ]);

        $chart->setOptions([
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => false,
                    'grace' => '10%',
                ],
            ],
        ]);

        return $chart;
    }
}
