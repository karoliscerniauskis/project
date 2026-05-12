<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Url;

use App\Voucher\Application\Url\FrontendUrlCreator as FrontendUrlCreatorInterface;

final readonly class FrontendUrlCreator implements FrontendUrlCreatorInterface
{
    private const string MY_VOUCHERS_FORMAT = '%s/vouchers';
    private const string PROVIDER_VOUCHERS_FORMAT = '%s/providers/%s/vouchers';

    public function __construct(
        private string $frontendUrl,
    ) {
    }

    public function myVouchers(): string
    {
        return sprintf(
            self::MY_VOUCHERS_FORMAT,
            $this->frontendUrl,
        );
    }

    public function providerVouchers(string $providerId): string
    {
        return sprintf(
            self::PROVIDER_VOUCHERS_FORMAT,
            $this->frontendUrl,
            rawurlencode($providerId),
        );
    }
}
