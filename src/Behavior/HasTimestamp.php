<?php

namespace App\Behavior;

interface HasTimestamp
{
    public function getCreatedAt(): \DateTimeImmutable;

    public function getUpdatedAt(): \DateTimeImmutable;

    public function touch(): void;
}
