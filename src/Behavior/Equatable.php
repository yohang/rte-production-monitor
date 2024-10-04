<?php
declare(strict_types=1);

namespace App\Behavior;

/**
 * @template T
 */
interface Equatable
{
    /**
     * @param T $other
     */
    public function equals($other): bool;
}
