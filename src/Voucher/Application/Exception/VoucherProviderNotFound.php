<?php

declare(strict_types=1);

namespace App\Voucher\Application\Exception;

use App\Shared\Application\Exception\AbstractApiException;
use App\Shared\Domain\Id\ProviderId;

final class VoucherProviderNotFound extends AbstractApiException
{
    private function __construct(string $value, string $key)
    {
        parent::__construct(
            'Provider was not found for this voucher.',
            [
                self::getError(
                    $key,
                    sprintf('Provider "%s" was not found for this voucher.', $value),
                ),
            ],
        );
    }

    public static function forId(ProviderId $providerId): self
    {
        return new self($providerId->toString(), 'providerId');
    }
}
