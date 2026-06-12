<?php

declare(strict_types=1);

namespace App\Bridge\RTE\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class CapacitiesPerProductionUnit
{
    public function __construct(
        /** @var array<int, CapacityPerProductionUnit> */
        #[SerializedName('capacities_per_production_unit')] public array $capacitiesPerProductionUnit,
    ) {
    }
}
