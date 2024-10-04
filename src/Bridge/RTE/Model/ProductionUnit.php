<?php
declare(strict_types=1);

namespace App\Bridge\RTE\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class ProductionUnit
{
    public function __construct(
        #[SerializedName('code_eic')] public string $eicCode,
        #[SerializedName('name')] public string $name,
        #[SerializedName('location')] public string $location,
        #[SerializedName('code_eic_producteur')] public ?string $producerEicCode,
        #[SerializedName('name_producteur')] public ?string $producerName,
    )
    {
    }
}
