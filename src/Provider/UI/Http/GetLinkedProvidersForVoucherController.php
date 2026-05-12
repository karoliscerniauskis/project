<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Query\GetLinkedProvidersForVoucher;
use App\Provider\Domain\View\LinkedProvidersView;
use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UuidValidator;
use App\Shared\UI\Http\InvalidRequestParameterException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetLinkedProvidersForVoucherController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly UuidValidator $uuidValidator,
    ) {
    }

    #[Route('/api/vouchers/{voucherId}/linked-providers', name: 'api_voucher_linked_providers_get', methods: ['GET'])]
    public function __invoke(string $voucherId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->uuidValidator->isValid($voucherId)) {
            throw new InvalidRequestParameterException('voucherId', sprintf('Invalid Voucher ID "%s".', $voucherId));
        }

        /** @var LinkedProvidersView $view */
        $view = $this->queryBus->ask(
            new GetLinkedProvidersForVoucher($voucherId, $user->getId()),
        );

        return new JsonResponse($view->toArray());
    }
}
