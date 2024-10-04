<?php
declare(strict_types=1);

namespace App\Controller\Chart;

use App\Entity\ProductionUnit;
use App\Repository\ProductionValueRepository;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[AsController]
#[Template('chart/production_unit_production_chart.html.twig')]
final readonly class ProductionUnitProductionChartController
{
    public function __construct(
        private ChartBuilderInterface     $chartBuilder,
        private ProductionValueRepository $productionValueRepository,
    )
    {
    }

    public function __invoke(ProductionUnit $productionUnit)
    {
        $dataset = $this->productionValueRepository->findForUnitBetweenDates(
            $productionUnit,
            new \DateTimeImmutable('-1 day -2 hour'),
            new \DateTimeImmutable('now'),
        );

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels'   => array_map(fn (array $value) => $value['startDate']->format('H:i'), $dataset),
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
            'scales' => [
                'y' => [
                    'beginAtZero' => false,
                    'grace' => '10%',
                ],
            ],
        ]);

        return [
            'chart' => $chart,
        ];
    }
}
