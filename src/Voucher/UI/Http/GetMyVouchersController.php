<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Voucher\Application\Query\GetMyVouchers;
use App\Voucher\Domain\View\MyVouchersView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetMyVouchersController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/api/me/vouchers', name: 'api_me_vouchers_list', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var MyVouchersView $myVouchersView */
        $myVouchersView = $this->queryBus->ask(
            new GetMyVouchers($user->getUserIdentifier(), $user->getId()),
        );

        return new JsonResponse(['data' => $myVouchersView->toArray()]);
    }
}
