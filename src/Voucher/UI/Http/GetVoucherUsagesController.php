<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UuidValidator;
use App\Shared\UI\Http\InvalidRequestParameterException;
use App\Voucher\Application\Query\GetVoucherUsages;
use App\Voucher\Domain\View\VoucherUsagesView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetVoucherUsagesController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly UuidValidator $uuidValidator,
    ) {
    }

    #[Route('/api/vouchers/{voucherId}/usages', name: 'api_voucher_usages_list', methods: ['GET'])]
    public function __invoke(string $voucherId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->uuidValidator->isValid($voucherId)) {
            throw new InvalidRequestParameterException('voucherId', sprintf('Invalid Voucher "%s".', $voucherId));
        }

        /** @var VoucherUsagesView $voucherUsagesView */
        $voucherUsagesView = $this->queryBus->ask(new GetVoucherUsages($voucherId));

        return new JsonResponse(['data' => $voucherUsagesView->toArray()]);
    }
}
