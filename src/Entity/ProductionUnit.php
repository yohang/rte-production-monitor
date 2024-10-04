<?php
declare(strict_types=1);

namespace App\Entity;

use App\Behavior\HasTimestamp;
use App\Behavior\Impl\TimestampImpl;
use App\Bridge\RTE\Model\ProductionUnit as RTEProductionUnit;
use App\Repository\ProductionUnitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: ProductionUnitRepository::class)]
#[Table]
#[HasLifecycleCallbacks]
class ProductionUnit implements HasTimestamp
{
    use TimestampImpl;

    #[Id]
    #[Column(type: UuidType::NAME)]
    #[GeneratedValue(strategy: 'NONE')]
    private Uuid $id;

    #[ManyToOne(targetEntity: Producer::class)]
    #[JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Producer $producer = null;

    #[Column(type: Types::STRING, length: 255, unique: true, nullable: false)]
    private string $eicCode;

    #[Column(type: Types::STRING, length: 255, nullable: false)]
    private string $name;

    #[Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $location = null;

    /**
     * @var Collection<int, ProductionSubUnit>
     */
    #[OrderBy(['name' => 'ASC'])]
    #[OneToMany(targetEntity: ProductionSubUnit::class, mappedBy: 'productionUnit')]
    private Collection $subUnits;

    /**
     * @var Collection<int, ProductionUnitValues>
     */
    #[OrderBy(['startDate' => 'ASC'])]
    #[OneToMany(targetEntity: ProductionUnitValues::class, mappedBy: 'productionUnit')]
    private Collection $values;

    public function __construct(string $eicCode, string $name)
    {
        $this->id       = Uuid::v6();
        $this->eicCode  = $eicCode;
        $this->name     = $name;
        $this->subUnits = new ArrayCollection;
        $this->values   = new ArrayCollection;

        $this->initialize();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getProducer(): ?Producer
    {
        return $this->producer;
    }

    public function setProducer(?Producer $producer): void
    {
        $this->producer = $producer;
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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    public function getSubUnits(): Collection
    {
        return $this->subUnits;
    }

    public function getValues(): Collection
    {
        return $this->values;
    }

    public function getLatestValues(): ProductionUnitValues
    {
        $values = $this->values->toArray();
        if (0 === count($values)) {
            throw new \RuntimeException('No values found for this production unit');
        }

        return array_reduce(
            $values,
            static function (ProductionUnitValues $latest, ProductionUnitValues $value): ProductionUnitValues {
                if ($value->getStartDate() > $latest->getStartDate()) {
                    return $value;
                }

                return $latest;
            },
            $values[0],
        );
    }

    public function syncWithRTE(RTEProductionUnit $rteProductionUnit): void
    {
        $this->setName($rteProductionUnit->name);
        $this->setLocation($rteProductionUnit->location);
    }

    public function __toString(): string
    {
        return $this->name ?: 'A production unit with no name';
    }

    public static function fromRTEProductionUnit(RTEProductionUnit $rteProductionUnit): self
    {
        $productionUnit = new self($rteProductionUnit->eicCode, $rteProductionUnit->name);
        $productionUnit->syncWithRTE($rteProductionUnit);

        return $productionUnit;
    }
}
