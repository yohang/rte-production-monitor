<?php

declare(strict_types=1);

namespace App\Importer;

final class ImportState
{
    private int $rowsProcessed = 0;

    public function __construct(public readonly int $rowsToProcess)
    {
    }

    public function processRow(): void
    {
        ++$this->rowsProcessed;
    }

    public function isFinished(): bool
    {
        return $this->rowsProcessed === $this->rowsToProcess;
    }

    public function getRowsProcessed(): int
    {
        return $this->rowsProcessed;
    }
}
