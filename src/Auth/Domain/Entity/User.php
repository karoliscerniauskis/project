<?php

declare(strict_types=1);

namespace App\Auth\Domain\Entity;

use App\Auth\Domain\Event\UserRegistered;
use App\Shared\Domain\Event\AbstractAggregateRoot;
use App\Shared\Domain\Id\UserId;
use DateTimeImmutable;

final class User extends AbstractAggregateRoot
{
    private UserId $id;
    private string $email;
    private string $hashedPassword;
    /** @var string[] */
    private array $roles;
    private ?string $emailVerificationSlug;
    private ?DateTimeImmutable $emailVerifiedAt = null;

    private function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string[] $roles
     */
    public static function register(
        UserId $id,
        string $email,
        string $hashedPassword,
        array $roles,
        string $emailVerificationSlug,
        DateTimeImmutable $occurredOn,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->email = $email;
        $self->hashedPassword = $hashedPassword;
        $self->roles = $roles;
        $self->emailVerificationSlug = $emailVerificationSlug;
        $self->emailVerifiedAt = null;
        $self->record(new UserRegistered($email, $emailVerificationSlug, $occurredOn));

        return $self;
    }

    public function getId(): UserId
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

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param string[] $roles
     */
    public static function reconstitute(
        UserId $id,
        string $email,
        string $hashedPassword,
        array $roles,
        ?string $emailVerificationSlug,
        ?DateTimeImmutable $emailVerifiedAt,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->email = $email;
        $self->hashedPassword = $hashedPassword;
        $self->roles = $roles;
        $self->emailVerificationSlug = $emailVerificationSlug;
        $self->emailVerifiedAt = $emailVerifiedAt;

        return $self;
    }

    public function getEmailVerificationSlug(): ?string
    {
        return $this->emailVerificationSlug;
    }

    public function getEmailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function verifyEmail(DateTimeImmutable $verifiedAt): void
    {
        if ($this->emailVerifiedAt !== null) {
            return;
        }

        $this->emailVerifiedAt = $verifiedAt;
        $this->emailVerificationSlug = null;
    }
}
