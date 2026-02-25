<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Entity;

use App\Shared\Domain\Event\AbstractAggregateRoot;

final class Voucher extends AbstractAggregateRoot
{
    private function __construct()
    {
        parent::__construct();
    }
}
