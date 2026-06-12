<?php

declare(strict_types=1);

namespace App\Bridge\RTE\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class EicData
{
    public function __construct(
        #[SerializedName('code_acer')] public ?string $acerCode,
        #[SerializedName('code_eic')] public string $eicCode,
        #[SerializedName('code_tva')] public ?string $vatCode,
        #[SerializedName('code_type')] public string $codeType,
        public ?string $country,
        #[SerializedName('displayed_name')] public string $displayedName,
        #[SerializedName('eic_parent')] public string $parentEicCode,
        #[SerializedName('responsible_eic')] public ?string $responsibleEicCode,
        #[SerializedName('entity_name')] public string $entityName,
        public ?string $function,
    ) {
    }
}
