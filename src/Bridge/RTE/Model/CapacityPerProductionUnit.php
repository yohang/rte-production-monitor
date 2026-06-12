<?php

declare(strict_types=1);

namespace App\Bridge\RTE\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class CapacityPerProductionUnit
{
    public function __construct(
        #[SerializedName('start_date')] public \DateTimeImmutable $startDate,
        #[SerializedName('end_date')] public \DateTimeImmutable $endDate,
        #[SerializedName('production_unit')] public ProductionUnit $productionUnit,
        /** @var CapacityPerProductionUnitValues[] */ public array $values,
    ) {
    }
}
