<?php

declare(strict_types=1);

namespace App\Controller\ProductionUnit;

use App\Entity\ProductionUnit;
use App\Repository\ProductionValueRepository;
use App\UX\Map\HomepageMapFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route('/production-unit/{eicCode}', name: 'production_unit_show')]
#[Template('production_unit/show.html.twig')]
final readonly class Show
{
    public function __construct(
        private HomepageMapFactory $homepageMapFactory,
        private ProductionValueRepository $productionValueRepository,
    ) {
    }

    public function __invoke(
        #[MapEntity(ProductionUnit::class, mapping: ['eicCode' => 'eicCode'])] ProductionUnit $productionUnit,
    ): array {
        $displayedProductionUnits = null !== $productionUnit->getFirstUnitOfGroup()
            ? $productionUnit->getSiblings()->toArray()
            : [$productionUnit];

        $lastProductionValuesByUnitId = $this->productionValueRepository->findLastValuesForProductionUnits($displayedProductionUnits);
        $lastProductionValuesByEicCode = [];

        foreach ($displayedProductionUnits as $displayedProductionUnit) {
            $lastProductionValuesByEicCode[$displayedProductionUnit->getEicCode()] = $lastProductionValuesByUnitId[$displayedProductionUnit->getId()->toRfc4122()] ?? null;
        }

        return [
            'production_unit' => $productionUnit,
            'displayed_production_units' => $displayedProductionUnits,
            'last_production_values_by_eic_code' => $lastProductionValuesByEicCode,
            'map' => $this->homepageMapFactory->create(),
        ];
    }
}
