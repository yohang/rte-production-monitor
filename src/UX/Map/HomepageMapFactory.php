<?php
declare(strict_types=1);

namespace App\UX\Map;

use App\Repository\ProductionUnitRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\Map\Bridge\Leaflet\LeafletOptions;
use Symfony\UX\Map\Bridge\Leaflet\Option\TileLayer;
use Symfony\UX\Map\InfoWindow;
use Symfony\UX\Map\Map;
use Symfony\UX\Map\Marker;
use Symfony\UX\Map\Point;

final readonly class HomepageMapFactory
{
    public function __construct(
        private ProductionUnitRepository $productionUnitRepository,
        private UrlGeneratorInterface    $urlGenerator,
    )
    {
    }

    public function create(): Map
    {
        $map = (new Map)
            ->fitBoundsToMarkers()
            ->options((new LeafletOptions())
                ->tileLayer(new TileLayer(
                    url: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                    options: ['maxZoom' => 19]
                ))
            );;

        foreach ($this->productionUnitRepository->findHavingLatitudeAndLongitude() as $productionUnit) {
            $map
                ->addMarker(
                    new Marker(
                        new Point($productionUnit->getLatitude(), $productionUnit->getLongitude()),
                        $productionUnit->getName(),
                        new InfoWindow(
                            $productionUnit->getName(),
                            sprintf(
                                '<a href="%s" data-turbo-frame="homepage-sidebar">Détail</a>',
                                $this->urlGenerator->generate(
                                    'production_unit_show',
                                    ['eicCode' => $productionUnit->getEicCode()],
                                ),
                            ),
                        ),
                    ),
                );
        }

        return $map;
    }
}
