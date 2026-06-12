<?php

namespace App\Behavior\Impl;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PreUpdate;

#[HasLifecycleCallbacks]
trait TimestampImpl
{
    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    final public function initialize(): void
    {
        /* @psalm-suppress RedundantPropertyInitializationCheck */
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[PreUpdate]
    final public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    final public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    final public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
