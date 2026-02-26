<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'voucher')]
class VoucherRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private string $id;

    #[ORM\Column(type: 'string', unique: true)]
    private string $code;

    public function __construct(
        string $id,
        string $code,
    ) {
        $this->id = $id;
        $this->code = $code;
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
