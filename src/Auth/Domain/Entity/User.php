<?php

declare(strict_types=1);

namespace App\Auth\Domain\Entity;

use App\Auth\Domain\Event\UserRegistered;
use App\Shared\Domain\Event\AbstractAggregateRoot;
use DateTimeImmutable;

final class User extends AbstractAggregateRoot
{
    private string $id;
    private string $email;
    private string $hashedPassword;

    private function __construct()
    {
        parent::__construct();
    }

    public static function register(
        string $id,
        string $email,
        string $hashedPassword,
        DateTimeImmutable $occurredOn,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->email = $email;
        $self->hashedPassword = $hashedPassword;
        $self->record(new UserRegistered($occurredOn));

        return $self;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getHashedPassword(): string
    {
        return $this->hashedPassword;
    }

    public static function reconstitute(string $id, string $email, string $hashedPassword): self
    {
        $self = new self();
        $self->id = $id;
        $self->email = $email;
        $self->hashedPassword = $hashedPassword;

        return $self;
    }
}
