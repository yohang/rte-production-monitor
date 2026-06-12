<?php

declare(strict_types=1);

namespace App\Bridge\RTE\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class ActualGenerationsPerUnit
{
    public function __construct(
        /** @var array<int,ActualGenerationPerUnit> */
        #[SerializedName('actual_generations_per_unit')] public array $actualGenerationsPerUnit,
    ) {
    }
}
