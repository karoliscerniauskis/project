<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Doctrine\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'auth_user')]
class UserRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;
    #[ORM\Column(type: 'string', unique: true)]
    private string $email;
    #[ORM\Column(type: 'string', unique: true, nullable: true)]
    private ?string $pendingEmail;
    #[ORM\Column(type: 'string')]
    private string $hashedPassword;
    /** @var string[] */
    #[ORM\Column(type: 'json')]
    private array $roles;
    #[ORM\Column(type: 'string', unique: true, nullable: true)]
    private ?string $emailVerificationSlug;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $emailVerifiedAt;
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $emailBreachCheckEnabled;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $emailBreachCheckedAt;
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $emailBreachedAt;
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $emailBreachCount;

    /**
     * @param string[] $roles
     */
    public function __construct(
        string $id,
        string $email,
        ?string $pendingEmail,
        string $hashedPassword,
        array $roles,
        ?string $emailVerificationSlug,
        ?DateTimeImmutable $emailVerifiedAt = null,
        bool $emailBreachCheckEnabled = false,
        ?DateTimeImmutable $emailBreachCheckedAt = null,
        ?DateTimeImmutable $emailBreachedAt = null,
        int $emailBreachCount = 0,
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->pendingEmail = $pendingEmail;
        $this->hashedPassword = $hashedPassword;
        $this->roles = $roles;
        $this->emailVerificationSlug = $emailVerificationSlug;
        $this->emailVerifiedAt = $emailVerifiedAt;
        $this->emailBreachCheckEnabled = $emailBreachCheckEnabled;
        $this->emailBreachCheckedAt = $emailBreachCheckedAt;
        $this->emailBreachedAt = $emailBreachedAt;
        $this->emailBreachCount = $emailBreachCount;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPendingEmail(): ?string
    {
        return $this->pendingEmail;
    }

    public function setPendingEmail(?string $pendingEmail): self
    {
        $this->pendingEmail = $pendingEmail;

        return $this;
    }

    public function getHashedPassword(): string
    {
        return $this->hashedPassword;
    }

    public function setHashedPassword(string $hashedPassword): self
    {
        $this->hashedPassword = $hashedPassword;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getEmailVerificationSlug(): ?string
    {
        return $this->emailVerificationSlug;
    }

    public function setEmailVerificationSlug(?string $emailVerificationSlug): self
    {
        $this->emailVerificationSlug = $emailVerificationSlug;

        return $this;
    }

    public function getEmailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function setEmailVerifiedAt(?DateTimeImmutable $emailVerifiedAt): self
    {
        $this->emailVerifiedAt = $emailVerifiedAt;

        return $this;
    }

    public function isEmailBreachCheckEnabled(): bool
    {
        return $this->emailBreachCheckEnabled;
    }

    public function setEmailBreachCheckEnabled(bool $emailBreachCheckEnabled): self
    {
        $this->emailBreachCheckEnabled = $emailBreachCheckEnabled;

        return $this;
    }

    public function getEmailBreachCheckedAt(): ?DateTimeImmutable
    {
        return $this->emailBreachCheckedAt;
    }

    public function setEmailBreachCheckedAt(?DateTimeImmutable $emailBreachCheckedAt): self
    {
        $this->emailBreachCheckedAt = $emailBreachCheckedAt;

        return $this;
    }

    public function getEmailBreachedAt(): ?DateTimeImmutable
    {
        return $this->emailBreachedAt;
    }

    public function setEmailBreachedAt(?DateTimeImmutable $emailBreachedAt): self
    {
        $this->emailBreachedAt = $emailBreachedAt;

        return $this;
    }

    public function getEmailBreachCount(): int
    {
        return $this->emailBreachCount;
    }

    public function setEmailBreachCount(int $emailBreachCount): self
    {
        $this->emailBreachCount = $emailBreachCount;

        return $this;
    }
}
