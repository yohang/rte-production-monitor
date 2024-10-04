<?php
declare(strict_types=1);

namespace App\Entity;

use App\Behavior\HasTimestamp;
use App\Behavior\Impl\TimestampImpl;
use App\Bridge\RTE\Model\CapacityPerProductionUnitValues;
use App\Bridge\RTE\Model\ProductionType;
use App\Repository\ProductionUnitValuesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: ProductionUnitValuesRepository::class)]
#[Table]
#[HasLifecycleCallbacks]
class ProductionUnitValues implements HasTimestamp
{
    use TimestampImpl;

    #[Id]
    #[Column(type: UuidType::NAME)]
    #[GeneratedValue(strategy: 'NONE')]
    private Uuid $id;

    #[ManyToOne(targetEntity: ProductionUnit::class, inversedBy: 'values')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ProductionUnit $productionUnit;

    #[Column(type: 'string', nullable: true, enumType: ProductionType::class)]
    private ?ProductionType $type = null;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private \DateTimeImmutable $startDate;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[Column(type: Types::FLOAT, nullable: false)]
    private float $installedCapacity;

    #[Column(type: Types::FLOAT, nullable: false)]
    private float $voltageLevelConnection;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedDate = null;

    public function __construct(ProductionUnit $productionUnit, \DateTimeImmutable $startDate, float $installedCapacity, float $voltageLevelConnection)
    {
        $this->id                     = Uuid::v6();
        $this->productionUnit         = $productionUnit;
        $this->startDate              = $startDate;
        $this->installedCapacity      = $installedCapacity;
        $this->voltageLevelConnection = $voltageLevelConnection;

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

    public function setProductionUnit(ProductionUnit $productionUnit): void
    {
        $this->productionUnit = $productionUnit;
    }

    public function getType(): ?ProductionType
    {
        return $this->type;
    }

    public function setType(?ProductionType $type): void
    {
        $this->type = $type;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getInstalledCapacity(): float
    {
        return $this->installedCapacity;
    }

    public function setInstalledCapacity(float $installedCapacity): void
    {
        $this->installedCapacity = $installedCapacity;
    }

    public function getVoltageLevelConnection(): float
    {
        return $this->voltageLevelConnection;
    }

    public function setVoltageLevelConnection(float $voltageLevelConnection): void
    {
        $this->voltageLevelConnection = $voltageLevelConnection;
    }

    public function getUpdatedDate(): ?\DateTimeImmutable
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(?\DateTimeImmutable $updatedDate): void
    {
        $this->updatedDate = $updatedDate;
    }

    public function syncWithRTE(CapacityPerProductionUnitValues $capacityPerProductionUnitValues): void
    {
        $this->setType($capacityPerProductionUnitValues->type);
        $this->setEndDate($capacityPerProductionUnitValues->endDate);
        $this->setInstalledCapacity($capacityPerProductionUnitValues->installedCapacity);
        $this->setVoltageLevelConnection($capacityPerProductionUnitValues->voltageLevelConnection);
        $this->setUpdatedDate($capacityPerProductionUnitValues->updatedDate);
    }

    public function __toString(): string
    {
        return sprintf(
            '%s / %s MWh / %s kV',
            $this->type?->value ?: 'Unknown',
            $this->installedCapacity,
            $this->voltageLevelConnection,
        );
    }

    public static function fromRTEProductionUnitValues(ProductionUnit $productionUnit, CapacityPerProductionUnitValues $productionUnitValues): self
    {
        $values = new self(
            $productionUnit,
            $productionUnitValues->startDate,
            $productionUnitValues->installedCapacity,
            $productionUnitValues->voltageLevelConnection
        );
        $values->syncWithRTE($productionUnitValues);

        return $values;
    }
}
