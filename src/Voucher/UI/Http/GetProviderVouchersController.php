<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use App\Voucher\Application\Query\GetProviderVouchers;
use App\Voucher\Domain\View\ProviderVouchersView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class GetProviderVouchersController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/api/providers/{providerId}/vouchers', name: 'api_provider_vouchers_list', methods: ['GET'])]
    public function __invoke(string $providerId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var ProviderVouchersView $providerVouchersView */
        $providerVouchersView = $this->queryBus->ask(
            new GetProviderVouchers(
                ProviderId::fromString($providerId),
                UserId::fromString($user->getId()),
            ),
        );

        return new JsonResponse(['data' => $providerVouchersView->toArray()]);
    }
}
