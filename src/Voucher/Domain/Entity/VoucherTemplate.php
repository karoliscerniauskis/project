<?php

declare(strict_types=1);

namespace App\Voucher\Domain\Entity;

use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\VoucherTemplateId;
use App\Voucher\Domain\Enum\VoucherType;
use DateTimeImmutable;
use LogicException;

final class VoucherTemplate
{
    private VoucherTemplateId $id;
    private ProviderId $providerId;
    private string $name;
    private VoucherType $type;
    private string $title;
    private string $description;
    private string $htmlTemplate;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    private function __construct()
    {
    }

    public static function create(
        VoucherTemplateId $id,
        ProviderId $providerId,
        string $name,
        VoucherType $type,
        string $title,
        string $description,
        string $htmlTemplate,
        DateTimeImmutable $occurredOn,
    ): self {
        self::assertNotBlank($name, 'Voucher template name cannot be blank.');
        self::assertNotBlank($title, 'Voucher template title cannot be blank.');
        self::assertNotBlank($description, 'Voucher template description cannot be blank.');
        self::assertNotBlank($htmlTemplate, 'Voucher template HTML cannot be blank.');
        self::assertRequiredPlaceholders($htmlTemplate, $type);

        $self = new self();
        $self->id = $id;
        $self->providerId = $providerId;
        $self->name = $name;
        $self->type = $type;
        $self->title = $title;
        $self->description = $description;
        $self->htmlTemplate = $htmlTemplate;
        $self->createdAt = $occurredOn;
        $self->updatedAt = $occurredOn;

        return $self;
    }

    public static function reconstitute(
        VoucherTemplateId $id,
        ProviderId $providerId,
        string $name,
        VoucherType $type,
        string $title,
        string $description,
        string $htmlTemplate,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        $self = new self();
        $self->id = $id;
        $self->providerId = $providerId;
        $self->name = $name;
        $self->type = $type;
        $self->title = $title;
        $self->description = $description;
        $self->htmlTemplate = $htmlTemplate;
        $self->createdAt = $createdAt;
        $self->updatedAt = $updatedAt;

        return $self;
    }

    public function update(
        string $name,
        VoucherType $type,
        string $title,
        string $description,
        string $htmlTemplate,
        DateTimeImmutable $occurredOn,
    ): void {
        self::assertNotBlank($name, 'Voucher template name cannot be blank.');
        self::assertNotBlank($title, 'Voucher template title cannot be blank.');
        self::assertNotBlank($description, 'Voucher template description cannot be blank.');
        self::assertNotBlank($htmlTemplate, 'Voucher template HTML cannot be blank.');
        self::assertRequiredPlaceholders($htmlTemplate, $type);

        $this->name = $name;
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
        $this->htmlTemplate = $htmlTemplate;
        $this->updatedAt = $occurredOn;
    }

    public function belongsToProvider(ProviderId $providerId): bool
    {
        return $this->providerId->equals($providerId);
    }

    public function supportsType(VoucherType $type): bool
    {
        return $this->type === $type;
    }

    public function getId(): VoucherTemplateId
    {
        return $this->id;
    }

    public function getProviderId(): ProviderId
    {
        return $this->providerId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): VoucherType
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

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private static function assertNotBlank(string $value, string $message): void
    {
        if (trim($value) === '') {
            throw new LogicException($message);
        }
    }

    private static function assertRequiredPlaceholders(string $htmlTemplate, VoucherType $type): void
    {
        $requiredPlaceholders = [
            'title',
            'description',
            'code',
            'provider_name',
            $type === VoucherType::Amount ? 'amount' : 'usage',
        ];

        foreach ($requiredPlaceholders as $placeholder) {
            if (!preg_match('/{{\s*'.preg_quote($placeholder, '/').'\s*}}/', $htmlTemplate)) {
                throw new LogicException(sprintf('Voucher template is missing required placeholder "{{ %s }}".', $placeholder));
            }
        }
    }
}
