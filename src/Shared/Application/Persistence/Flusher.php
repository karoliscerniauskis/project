<?php

declare(strict_types=1);

namespace App\Shared\Application\Persistence;

interface Flusher
{
    public function flush(): void;
}
