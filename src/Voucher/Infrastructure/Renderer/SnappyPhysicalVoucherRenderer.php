<?php

declare(strict_types=1);

namespace App\Voucher\Infrastructure\Renderer;

use App\Voucher\Application\Service\PhysicalVoucherRenderer;
use App\Voucher\Domain\Entity\Voucher;
use Knp\Snappy\Image;
use Twig\Environment;

final readonly class SnappyPhysicalVoucherRenderer implements PhysicalVoucherRenderer
{
    public function __construct(
        private Image $snappy,
        private Environment $twig,
    ) {
    }

    public function render(Voucher $voucher, string $providerName): string
    {
        $html = $this->twig->render('voucher/physical.html.twig', [
            'providerName' => $providerName,
            'code' => $voucher->getCode(),
            'valueText' => $this->buildValueText($voucher),
            'expirationText' => $this->buildExpirationText($voucher),
        ]);

        $imageData = $this->snappy->getOutputFromHtml($html, [
            'width' => 850,
            'quality' => 100,
        ]);

        return 'data:image/png;base64,'.base64_encode($imageData);
    }

    private function buildValueText(Voucher $voucher): string
    {
        if ($voucher->getRemainingAmount() !== null) {
            $amount = $voucher->getRemainingAmount() / 100;

            return '€'.number_format($amount, 2);
        }

        if ($voucher->getRemainingUsages() !== null) {
            return $voucher->getRemainingUsages().' uses';
        }

        return '';
    }

    private function buildExpirationText(Voucher $voucher): string
    {
        if ($voucher->getExpiresAt() === null) {
            return 'No expiration';
        }

        return $voucher->getExpiresAt()->format('Y-m-d');
    }
}
