<?php

declare(strict_types=1);

namespace App\UX\Map;

use App\Repository\ProductionUnitRepository;
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

        foreach ($this->productionUnitRepository->findHavingLatitudeAndLongitude() as $productionUnit) {
            /*
             * @psalm-suppress PossiblyNullArgument
             */

            if (
                null !== $productionUnit->getFirstUnitOfGroup()
                && !$productionUnit->equals($productionUnit->getFirstUnitOfGroup())
            ) {
                continue;
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
                                ['production_unit' => $productionUnit],
                            ),
                        ),
                        [
                            'icon' => $this->packages->getUrl(
                                'icons/'.$productionUnit->getValues()[0]->getType()->getIconIdentifier().'.svg'
                            ),
                        ]
                    ),
                );
        }

        return $map;
    }
}
