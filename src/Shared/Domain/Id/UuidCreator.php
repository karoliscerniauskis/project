<?php

declare(strict_types=1);

namespace App\Shared\Domain\Id;

interface UuidCreator
{
    public function create(): string;
}
