<?php

declare(strict_types=1);

namespace App\Entity;

use App\Behavior\HasTimestamp;
use App\Behavior\Impl\TimestampImpl;
use App\Bridge\RTE\Model\ActualGenerationPerUnitValues;
use App\Repository\ProductionValueRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: ProductionValueRepository::class)]
#[Table]
#[HasLifecycleCallbacks]
#[UniqueConstraint(name: 'production_value_sub_unit_start_date_unique', fields: ['productionSubUnit', 'startDate'])]
class ProductionValue implements HasTimestamp
{
    use TimestampImpl;

    #[Id]
    #[Column(type: UuidType::NAME)]
    #[GeneratedValue(strategy: 'NONE')]
    private Uuid $id;

    #[ManyToOne(targetEntity: ProductionSubUnit::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ProductionSubUnit $productionSubUnit;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private \DateTimeImmutable $startDate;

    #[Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    #[Column(type: Types::INTEGER, nullable: false)]
    private int $value;

    public function __construct(ProductionSubUnit $productionSubUnit, \DateTimeImmutable $startDate, int $value)
    {
        $this->id = Uuid::v6();
        $this->productionSubUnit = $productionSubUnit;
        $this->startDate = $startDate;
        $this->value = $value;

        $this->initialize();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getProductionSubUnit(): ?ProductionSubUnit
    {
        return $this->productionSubUnit;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeImmutable $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    public function syncWithRTE(ActualGenerationPerUnitValues $actualGenerationPerUnitValues): void
    {
        $this->setEndDate($actualGenerationPerUnitValues->endDate);
        $this->setValue($actualGenerationPerUnitValues->value);
    }

    public static function fromRTEProductionUnit(ProductionSubUnit $subUnit, ActualGenerationPerUnitValues $actualGenerationPerUnitValues): self
    {
        return new self($subUnit, $actualGenerationPerUnitValues->startDate, $actualGenerationPerUnitValues->value);
    }
}
