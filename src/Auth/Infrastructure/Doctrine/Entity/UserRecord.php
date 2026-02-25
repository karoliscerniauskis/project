<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Doctrine\Entity;

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
    #[ORM\Column(type: 'string')]
    private string $hashedPassword;

    public function __construct(
        string $id,
        string $email,
        string $hashedPassword,
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->hashedPassword = $hashedPassword;
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
}
