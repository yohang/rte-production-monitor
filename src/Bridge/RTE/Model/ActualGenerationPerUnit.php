<?php

declare(strict_types=1);

namespace App\Bridge\RTE\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class ActualGenerationPerUnit
{
    public function __construct(
        #[SerializedName('start_date')] public \DateTimeImmutable $startDate,
        #[SerializedName('end_date')] public \DateTimeImmutable $endDate,
        /** @var array<int,ActualGenerationPerUnitValues> */
        #[SerializedName('values')] public array $values,
    ) {
    }
}
