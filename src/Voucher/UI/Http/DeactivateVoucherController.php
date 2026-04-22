<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Voucher\Application\Command\DeactivateVoucher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeactivateVoucherController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
    }

    #[Route('/api/providers/{providerId}/vouchers/{code}/deactivate', name: 'api_provider_vouchers_deactivate', methods: ['POST'])]
    public function __invoke(string $providerId, string $code): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        $this->commandBus->dispatch(
            new DeactivateVoucher(
                $providerId,
                $code,
                $user->getId(),
            ),
        );

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
