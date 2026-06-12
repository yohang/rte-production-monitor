<?php

declare(strict_types=1);

namespace App\UX\Map;

use App\Entity\ProductionUnit;
use App\Repository\ProductionUnitValuesRepository;
use App\Repository\ProductionUnitRepository;
use App\Repository\ProductionValueRepository;
use Symfony\Component\Asset\Packages;
use Symfony\UX\Map\Bridge\Leaflet\LeafletOptions;
use Symfony\UX\Map\Bridge\Leaflet\Option\TileLayer;
use Symfony\UX\Map\InfoWindow;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;
use Twig\Environment;

final readonly class HomepageMapFactory
{
    public function __construct(
        private ProductionUnitRepository $productionUnitRepository,
        private ProductionValueRepository $productionValueRepository,
        private ProductionUnitValuesRepository $productionUnitValuesRepository,
        private Environment $twig,
        private Packages $packages,
    ) {
    }

    public function create(): Map
    {
        $map = (new Map())
            ->fitBoundsToMarkers()
            ->options((new LeafletOptions())
                ->tileLayer(new TileLayer(
                    url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                    options: ['maxZoom' => 19]
                ))
            );

        $displayedProductionUnits = array_values(array_filter(
            $this->productionUnitRepository->findHavingLatitudeAndLongitude(),
            static function (ProductionUnit $productionUnit): bool {
                $firstUnitOfGroup = $productionUnit->getFirstUnitOfGroup();

                return null === $firstUnitOfGroup || $productionUnit->equals($firstUnitOfGroup);
            },
        ));

        $groupMembersByFirstUnitId = $this->getGroupMembersByFirstUnitId($displayedProductionUnits);
        $allRelatedProductionUnits = $this->getAllRelatedProductionUnits($displayedProductionUnits, $groupMembersByFirstUnitId);
        $lastProductionValuesByUnitId = $this->productionValueRepository->findLastValuesForProductionUnits($allRelatedProductionUnits);
        $installedCapacitiesByUnitId = $this->productionUnitValuesRepository->findInstalledCapacitiesForProductionUnits($allRelatedProductionUnits);
        $latestTypesByUnitId = $this->productionUnitValuesRepository->findLatestTypesForProductionUnits($allRelatedProductionUnits);

        foreach ($displayedProductionUnits as $productionUnit) {
            $productionUnitId = $productionUnit->getId()->toRfc4122();
            $groupMembers = $groupMembersByFirstUnitId[$productionUnitId] ?? [$productionUnit];
            $iconIdentifier = 'mdi/electricity';
            $production = .0;
            $capacity = .0;

            foreach ($groupMembers as $groupMember) {
                $groupMemberId = $groupMember->getId()->toRfc4122();
                $production += $lastProductionValuesByUnitId[$groupMemberId] ?? .0;
                $capacity += $installedCapacitiesByUnitId[$groupMemberId] ?? .0;

                if ('mdi/electricity' === $iconIdentifier && isset($latestTypesByUnitId[$groupMemberId])) {
                    $iconIdentifier = $latestTypesByUnitId[$groupMemberId]?->getIconIdentifier() ?? $iconIdentifier;
                }
            }

            $map
                ->addMarker(
                    new Marker(
                        new Point($productionUnit->getLatitude(), $productionUnit->getLongitude()),
                        $productionUnit->getCanonicalName(),
                        new InfoWindow(
                            $productionUnit->getCanonicalName(),
                            $this->twig->render(
                                'production_unit/_info_window.html.twig',
                                [
                                    'capacity' => $capacity,
                                    'production' => $production,
                                    'production_unit' => $productionUnit,
                                ],
                            ),
                        ),
                        [
                            'icon' => $this->packages->getUrl(
                                'icons/'.$iconIdentifier.'.svg'
                            ),
                        ]
                    ),
                );
        }

        return $map;
    }

    /**
     * @param array<int, ProductionUnit> $displayedProductionUnits
     *
     * @return array<string, array<string, ProductionUnit>>
     */
    private function getGroupMembersByFirstUnitId(array $displayedProductionUnits): array
    {
        $groupedProductionUnits = $this->productionUnitRepository->findByFirstUnitsOfGroup(array_values(array_filter(
            $displayedProductionUnits,
            static fn (ProductionUnit $productionUnit): bool => null !== $productionUnit->getFirstUnitOfGroup(),
        )));

        $groupMembersByFirstUnitId = [];

        foreach ($displayedProductionUnits as $displayedProductionUnit) {
            $groupMembersByFirstUnitId[$displayedProductionUnit->getId()->toRfc4122()] = [
                $displayedProductionUnit->getId()->toRfc4122() => $displayedProductionUnit,
            ];
        }

        foreach ($groupedProductionUnits as $groupedProductionUnit) {
            $firstUnitOfGroup = $groupedProductionUnit->getFirstUnitOfGroup();
            if (null === $firstUnitOfGroup) {
                continue;
            }

            $groupMembersByFirstUnitId[$firstUnitOfGroup->getId()->toRfc4122()][$groupedProductionUnit->getId()->toRfc4122()] = $groupedProductionUnit;
        }

        return $groupMembersByFirstUnitId;
    }

    /**
     * @param array<int, ProductionUnit> $displayedProductionUnits
     * @param array<string, array<string, ProductionUnit>> $groupMembersByFirstUnitId
     *
     * @return array<int, ProductionUnit>
     */
    private function getAllRelatedProductionUnits(array $displayedProductionUnits, array $groupMembersByFirstUnitId): array
    {
        $allRelatedProductionUnitsById = [];

        foreach ($displayedProductionUnits as $displayedProductionUnit) {
            $allRelatedProductionUnitsById[$displayedProductionUnit->getId()->toRfc4122()] = $displayedProductionUnit;
        }

        foreach ($groupMembersByFirstUnitId as $groupMembers) {
            foreach ($groupMembers as $groupMemberId => $groupMember) {
                $allRelatedProductionUnitsById[$groupMemberId] = $groupMember;
            }
        }

        return array_values($allRelatedProductionUnitsById);
    }
}
