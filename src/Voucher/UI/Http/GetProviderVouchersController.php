<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\UserId;
use App\Shared\Domain\Id\UuidValidator;
use App\Shared\UI\Http\InvalidRequestParameterException;
use App\Voucher\Application\Query\GetProviderVouchers;
use App\Voucher\Domain\View\ProviderVouchersView;
use App\Voucher\UI\Http\OpenApi\ProviderVouchersResponse;
use App\Voucher\UI\Http\OpenApi\VoucherAccessDeniedResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetProviderVouchersController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly UuidValidator $uuidValidator,
    ) {
    }

    #[Route('/api/providers/{providerId}/vouchers', name: 'api_provider_vouchers_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/providers/{providerId}/vouchers',
        description: 'Returns vouchers for the selected provider. The authenticated user must be a provider member.',
        summary: 'List provider vouchers',
        security: [['Bearer' => []]],
        tags: ['Voucher'],
    )]
    #[OA\Parameter(
        name: 'providerId',
        description: 'Provider identifier.',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            format: 'uuid',
            example: '019d882d-1d68-7e2f-94ce-0cd2f4d0c369',
        ),
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Provider vouchers returned successfully.',
        content: new OA\JsonContent(ref: new Model(type: ProviderVouchersResponse::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_FORBIDDEN,
        description: 'Provider access is required.',
        content: new OA\JsonContent(ref: new Model(type: VoucherAccessDeniedResponse::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Invalid provider identifier.',
    )]
    public function __invoke(string $providerId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->uuidValidator->isValid($providerId)) {
            throw new InvalidRequestParameterException('providerId', sprintf('Invalid Provider "%s".', $providerId));
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
