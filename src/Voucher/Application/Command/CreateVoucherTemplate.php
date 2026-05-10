<?php

declare(strict_types=1);

namespace App\Voucher\Application\Command;

final readonly class CreateVoucherTemplate
{
    public function __construct(
        private string $voucherTemplateId,
        private string $providerId,
        private string $createdByUserId,
        private string $name,
        private string $type,
        private string $title,
        private string $description,
        private string $htmlTemplate,
    ) {
    }

    public function getVoucherTemplateId(): string
    {
        return $this->voucherTemplateId;
    }

    public function getProviderId(): string
    {
        return $this->providerId;
    }

    public function getCreatedByUserId(): string
    {
        return $this->createdByUserId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getHtmlTemplate(): string
    {
        return $this->htmlTemplate;
    }
}
