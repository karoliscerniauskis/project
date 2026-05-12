<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UuidValidator;
use App\Shared\UI\Http\InvalidRequestParameterException;
use App\Voucher\Application\Command\DeactivateVoucher;
use App\Voucher\UI\Http\OpenApi\VoucherAccessDeniedResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeactivateVoucherController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly UuidValidator $uuidValidator,
    ) {
    }

    #[Route('/api/providers/{providerId}/vouchers/{voucherId}/deactivate', name: 'api_provider_vouchers_deactivate', methods: ['POST'])]
    #[OA\Post(
        path: '/api/providers/{providerId}/vouchers/{voucherId}/deactivate',
        description: 'Deactivates a voucher by its identifier. The authenticated user must be a provider member.',
        summary: 'Deactivate voucher',
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
    #[OA\Parameter(
        name: 'voucherId',
        description: 'Voucher identifier.',
        in: 'path',
        required: true,
        schema: new OA\Schema(
            type: 'string',
            format: 'uuid',
            example: '019d882d-1d68-7e2f-94ce-0cd2f4d0c369',
        ),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Voucher deactivated successfully.',
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
    public function __invoke(string $providerId, string $voucherId): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->uuidValidator->isValid($providerId)) {
            throw new InvalidRequestParameterException('providerId', sprintf('Invalid Provider "%s".', $providerId));
        }

        if (!$this->uuidValidator->isValid($voucherId)) {
            throw new InvalidRequestParameterException('voucherId', sprintf('Invalid Voucher "%s".', $voucherId));
        }

        $this->commandBus->dispatch(
            new DeactivateVoucher(
                $providerId,
                $voucherId,
                $user->getId(),
            ),
        );

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
