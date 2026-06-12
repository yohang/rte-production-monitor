<?php

declare(strict_types=1);

namespace App\Bridge\RTE\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class CapacityPerProductionUnitValues
{
    public function __construct(
        #[SerializedName('start_date')] public \DateTimeImmutable $startDate,
        #[SerializedName('end_date')] public ?\DateTimeImmutable $endDate,
        #[SerializedName('installed_capacity')] public float $installedCapacity,
        #[SerializedName('voltage_level_connection')] public float $voltageLevelConnection,
        #[SerializedName('type')] public ?ProductionType $type,
        #[SerializedName('updated_date')] public ?\DateTimeImmutable $updatedDate,
    ) {
    }
}
