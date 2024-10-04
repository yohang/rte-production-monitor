<?php

namespace App\Behavior\Impl;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

#[HasLifecycleCallbacks]
trait TimestampImpl
{
    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public final function initialize(): void
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        $this->createdAt = new \DateTimeImmutable;
        $this->updatedAt = new \DateTimeImmutable;
    }

    #[PreUpdate]
    public final function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable;
    }

    public final function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public final function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
