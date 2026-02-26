<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Entity;

use App\Shared\Domain\Event\AbstractAggregateRoot;
use App\Voucher\Domain\Event\VoucherCreated;
use DateTimeImmutable;

final class Voucher extends AbstractAggregateRoot
{
    private string $id;
    private string $code;

    private function __construct()
    {
        parent::__construct();
    }

    public static function create(
        string $id,
        string $code,
        DateTimeImmutable $occurredOn,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->code = $code;
        $self->record(new VoucherCreated($occurredOn));

        return $self;
    }

    public static function reconstitute(string $id, string $code): self
    {
        $self = new self();
        $self->id = $id;
        $self->code = $code;

        return $self;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
