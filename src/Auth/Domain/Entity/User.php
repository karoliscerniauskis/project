<?php

declare(strict_types=1);

namespace App\Auth\Domain\Entity;

use App\Auth\Domain\Event\UserEmailChangeRequested;
use App\Auth\Domain\Event\UserRegistered;
use App\Shared\Domain\Event\AbstractAggregateRoot;
use App\Shared\Domain\Id\UserId;
use DateTimeImmutable;

final class User extends AbstractAggregateRoot
{
    private UserId $id;
    private string $email;
    private ?string $pendingEmail = null;
    private string $hashedPassword;
    /** @var string[] */
    private array $roles;
    private ?string $emailVerificationSlug;
    private ?DateTimeImmutable $emailVerifiedAt = null;
    private bool $emailBreachCheckEnabled = false;
    private ?DateTimeImmutable $emailBreachCheckedAt = null;
    private ?DateTimeImmutable $emailBreachedAt = null;
    private int $emailBreachCount = 0;

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
        $self->pendingEmail = null;
        $self->hashedPassword = $hashedPassword;
        $self->roles = $roles;
        $self->emailVerificationSlug = $emailVerificationSlug;
        $self->emailVerifiedAt = null;
        $self->emailBreachCheckEnabled = false;
        $self->emailBreachCheckedAt = null;
        $self->emailBreachedAt = null;
        $self->emailBreachCount = 0;
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

    public function getPendingEmail(): ?string
    {
        return $this->pendingEmail;
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
        ?string $pendingEmail,
        string $hashedPassword,
        array $roles,
        ?string $emailVerificationSlug,
        ?DateTimeImmutable $emailVerifiedAt,
        bool $emailBreachCheckEnabled = false,
        ?DateTimeImmutable $emailBreachCheckedAt = null,
        ?DateTimeImmutable $emailBreachedAt = null,
        int $emailBreachCount = 0,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->email = $email;
        $self->pendingEmail = $pendingEmail;
        $self->hashedPassword = $hashedPassword;
        $self->roles = $roles;
        $self->emailVerificationSlug = $emailVerificationSlug;
        $self->emailVerifiedAt = $emailVerifiedAt;
        $self->emailBreachCheckEnabled = $emailBreachCheckEnabled;
        $self->emailBreachCheckedAt = $emailBreachCheckedAt;
        $self->emailBreachedAt = $emailBreachedAt;
        $self->emailBreachCount = $emailBreachCount;

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
        if ($this->pendingEmail === null && $this->emailVerifiedAt !== null) {
            return;
        }

        if ($this->pendingEmail !== null) {
            $this->email = $this->pendingEmail;
            $this->pendingEmail = null;
        }

        $this->emailVerifiedAt = $verifiedAt;
        $this->emailVerificationSlug = null;
    }

    public function requestEmailChange(string $newEmail, string $emailVerificationSlug, DateTimeImmutable $occurredOn): void
    {
        if ($this->email === $newEmail) {
            return;
        }

        $this->pendingEmail = $newEmail;
        $this->emailVerificationSlug = $emailVerificationSlug;
        $this->record(new UserEmailChangeRequested($newEmail, $emailVerificationSlug, $occurredOn));
    }

    public function changePassword(string $hashedPassword): void
    {
        $this->hashedPassword = $hashedPassword;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function isEmailBreachCheckEnabled(): bool
    {
        return $this->emailBreachCheckEnabled;
    }

    public function getEmailBreachCheckedAt(): ?DateTimeImmutable
    {
        return $this->emailBreachCheckedAt;
    }

    public function getEmailBreachedAt(): ?DateTimeImmutable
    {
        return $this->emailBreachedAt;
    }

    public function getEmailBreachCount(): int
    {
        return $this->emailBreachCount;
    }

    public function configureEmailBreachCheck(bool $enabled): void
    {
        $this->emailBreachCheckEnabled = $enabled;
    }

    public function markEmailBreachCheckCompleted(
        DateTimeImmutable $checkedAt,
        bool $breached,
        int $breachCount,
    ): void {
        $this->emailBreachCheckedAt = $checkedAt;
        $this->emailBreachCount = $breachCount;
        $this->emailBreachedAt = $breached ? $checkedAt : null;
    }
}
