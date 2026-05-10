<?php

declare(strict_types=1);

namespace App\Voucher\Application\Handler;

use App\Shared\Application\Provider\ProviderStatusChecker;
use App\Shared\Application\ProviderUser\ProviderUserFinder;
use App\Shared\Domain\Clock\Clock;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\VoucherTemplateId;
use App\Voucher\Application\Command\CreateVoucherTemplate;
use App\Voucher\Application\Exception\ProviderInactive;
use App\Voucher\Application\Exception\ProviderUserNotFound;
use App\Voucher\Application\Validation\VoucherTemplateDataValidator;
use App\Voucher\Domain\Entity\VoucherTemplate;
use App\Voucher\Domain\Enum\VoucherType;
use App\Voucher\Domain\Repository\VoucherTemplateRepository;

final readonly class CreateVoucherTemplateHandler
{
    public function __construct(
        private VoucherTemplateRepository $voucherTemplateRepository,
        private Clock $clock,
        private ProviderUserFinder $providerUserFinder,
        private ProviderStatusChecker $providerStatusChecker,
        private VoucherTemplateDataValidator $voucherTemplateDataValidator,
    ) {
    }

    public function __invoke(CreateVoucherTemplate $command): void
    {
        $providerId = ProviderId::fromString($command->getProviderId());
        $createdByUserId = UserId::fromString($command->getCreatedByUserId());

        if (!$this->providerStatusChecker->isActive($providerId)) {
            throw ProviderInactive::create();
        }

        $providerUserId = $this->providerUserFinder->findIdByProviderIdAndUserId($providerId, $createdByUserId);

        if ($providerUserId === null) {
            throw ProviderUserNotFound::forProviderAndUserId($providerId->toString(), $createdByUserId->toString());
        }

        $type = VoucherType::from($command->getType());

        $this->voucherTemplateDataValidator->validate(
            $command->getName(),
            $type,
            $command->getTitle(),
            $command->getDescription(),
            $command->getHtmlTemplate(),
        );

        $voucherTemplate = VoucherTemplate::create(
            VoucherTemplateId::fromString($command->getVoucherTemplateId()),
            $providerId,
            $command->getName(),
            $type,
            $command->getTitle(),
            $command->getDescription(),
            $command->getHtmlTemplate(),
            $this->clock->now(),
        );

        $this->voucherTemplateRepository->save($voucherTemplate);
    }
}
