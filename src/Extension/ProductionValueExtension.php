<?php

declare(strict_types=1);

namespace App\Extension;

use App\Entity\ProductionUnit;
use App\Repository\ProductionValueRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ProductionValueExtension extends AbstractExtension
{
    public function __construct(
        private readonly ProductionValueRepository $productionValueRepository,
    ) {
    }

    /**
     * @return array<int, TwigFunction>
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_last_production_value_for_unit', $this->getProductionValueForUnit(...)),
            new TwigFunction('get_last_production_value_for_unit_group', $this->getProductionValueForGroup(...)),
        ];
    }

    public function getProductionValueForUnit(ProductionUnit $productionUnit): ?float
    {
        return $this->productionValueRepository->findLastValueForProductionUnit($productionUnit);
    }

    public function getProductionValueForGroup(ProductionUnit $productionUnit): ?float
    {
        if (null === $productionUnit->getFirstUnitOfGroup()) {
            return $this->productionValueRepository->findLastValueForProductionUnit($productionUnit);
        }

        return array_sum(
            array_map(
                fn (ProductionUnit $unit) => $this->productionValueRepository->findLastValueForProductionUnit($unit),
                $productionUnit->getSiblings()->toArray(),
            ),
        );
    }
}
