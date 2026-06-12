<?php

declare(strict_types=1);

namespace App\Extension;

use App\Entity\ProductionUnit;
use App\UX\Chart\ProductionChartFactory;
use Symfony\UX\Chartjs\Model\Chart;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ChartExtension extends AbstractExtension
{
    public function __construct(
        private ProductionChartFactory $productionChartFactory,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('production_chart', $this->getProductionChart(...)),
        ];
    }

    public function getProductionChart(ProductionUnit $productionUnit): Chart
    {
        return $this->productionChartFactory->create($productionUnit);
    }
}
