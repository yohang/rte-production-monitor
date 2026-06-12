<?php

declare(strict_types=1);

namespace App\Controller\ProductionUnit;

use App\Entity\ProductionUnit;
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
    ) {
    }

    public function __invoke(
        #[MapEntity(ProductionUnit::class, mapping: ['eicCode' => 'eicCode'])] ProductionUnit $productionUnit,
    ): array {
        return [
            'production_unit' => $productionUnit,
            'map' => $this->homepageMapFactory->create(),
        ];
    }
}
