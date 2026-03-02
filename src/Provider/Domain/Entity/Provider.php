<?php

declare(strict_types=1);

namespace App\Provider\Domain\Entity;

use App\Shared\Domain\Event\AbstractAggregateRoot;

final class Provider extends AbstractAggregateRoot
{
    private string $id;
    private string $name;
    private string $status;

    private function __construct()
    {
        parent::__construct();
    }

    public static function create(
        string $id,
        string $name,
        string $status,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->name = $name;
        $self->status = $status;

        return $self;
    }

    public static function reconstitute(
        string $id,
        string $name,
        string $status,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->name = $name;
        $self->status = $status;

        return $self;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
