<?php
declare(strict_types=1);

namespace App\Entity;

use App\Bridge\RTE\Model\ProductionUnit as RTEProductionUnit;
use App\Behavior\Impl\TimestampImpl;
use App\Repository\ProducerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: ProducerRepository::class)]
#[Table]
#[HasLifecycleCallbacks]
class Producer
{
    use TimestampImpl;

    #[Id]
    #[Column(type: UuidType::NAME)]
    #[GeneratedValue(strategy: 'NONE')]
    private Uuid $id;

    #[Column(type: Types::STRING, length: 255, unique: true, nullable: false)]
    private string $eicCode;

    #[Column(type: Types::STRING, length: 255, nullable: false)]
    private string $name;

    public function __construct(string $eicCode, string $name)
    {
        $this->id      = Uuid::v6();
        $this->eicCode = $eicCode;
        $this->name    = $name;

        $this->initialize();
    }

    public function getId(): Uuid
    {
        return $this->id;
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

    public function syncWithRTE(RTEProductionUnit $rteProductionUnit): void
    {
        if (null === $rteProductionUnit->producerName) {
            throw new \InvalidArgumentException('The RTE production unit must have a producer EIC code and a producer name');
        }

        $this->setName($rteProductionUnit->producerName);
    }

    public function __toString(): string
    {
        return $this->name ?: 'A producer with no name';
    }

    public static function fromRTEProductionUnit(RTEProductionUnit $rteProductionUnit): self
    {
        if (null === $rteProductionUnit->producerEicCode || null === $rteProductionUnit->producerName) {
            throw new \InvalidArgumentException('The RTE production unit must have a producer EIC code and a producer name');
        }

        $productionUnit = new self($rteProductionUnit->producerEicCode, $rteProductionUnit->producerName);
        $productionUnit->syncWithRTE($rteProductionUnit);

        return $productionUnit;
    }
}
