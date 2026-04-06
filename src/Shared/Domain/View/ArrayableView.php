<?php

declare(strict_types=1);

namespace App\Shared\Domain\View;

/**
 * @template T of array
 */
interface ArrayableView
{
    /**
     * @return T
     */
    public function toArray(): array;
}
