<?php
declare(strict_types=1);

namespace App\Entity;

use App\Behavior\HasTimestamp;
use App\Behavior\Impl\TimestampImpl;
use App\Bridge\RTE\Model\EicData;
use App\Repository\ProductionSubUnitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: ProductionSubUnitRepository::class)]
#[Table]
#[HasLifecycleCallbacks]
#[Index(name: 'production_sub_unit_unit_eic_index', fields: ['productionUnit', 'eicCode'])]
class ProductionSubUnit implements HasTimestamp
{
    use TimestampImpl;

    #[Id]
    #[Column(type: UuidType::NAME)]
    #[GeneratedValue(strategy: 'NONE')]
    private Uuid $id;

    #[ManyToOne(targetEntity: ProductionUnit::class, inversedBy: 'subUnits')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ProductionUnit $productionUnit;

    #[Column(type: Types::STRING, length: 255, unique: true, nullable: false)]
    private string $eicCode;

    #[Column(type: Types::STRING, length: 255, nullable: false)]
    private string $name;

    public function __construct(ProductionUnit $productionUnit, string $eicCode, string $name)
    {
        $this->id             = Uuid::v6();
        $this->productionUnit = $productionUnit;
        $this->eicCode        = $eicCode;
        $this->name           = $name;

        $this->initialize();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getProductionUnit(): ProductionUnit
    {
        return $this->productionUnit;
    }

    public function getEicCode(): string
    {
        return $this->eicCode;
    }

    public function setEicCode(string $eicCode): void
    {
        $this->eicCode = $eicCode;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function syncWithRTE(EicData $eicData): void
    {
        $this->setName($eicData->entityName);
    }

    public function __toString(): string
    {
        return $this->name ?: 'A production sub unit with no name';
    }

    public static function fromEicData(ProductionUnit $productionUnit, EicData $eicData): self
    {
        $productionUnit = new self($productionUnit, $eicData->eicCode, $eicData->entityName);
        $productionUnit->syncWithRTE($eicData);

        return $productionUnit;
    }
}
