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
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->pendingEmail = $pendingEmail;
        $this->hashedPassword = $hashedPassword;
        $this->roles = $roles;
        $this->emailVerificationSlug = $emailVerificationSlug;
        $this->emailVerifiedAt = $emailVerifiedAt;
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
}
