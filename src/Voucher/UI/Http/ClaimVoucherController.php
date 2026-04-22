<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Voucher\Application\Command\ClaimVoucher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ClaimVoucherController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
    }

    #[Route('/api/vouchers/{voucherId}/claim', name: 'api_voucher_claim', methods: ['POST'])]
    public function __invoke(string $voucherId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        $this->commandBus->dispatch(
            new ClaimVoucher(
                $voucherId,
                $user->getId(),
                $user->getUserIdentifier(),
            ),
        );

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
