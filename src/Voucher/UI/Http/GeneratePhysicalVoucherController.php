<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UuidValidator;
use App\Shared\UI\Http\InvalidRequestParameterException;
use App\Voucher\Application\Query\GeneratePhysicalVoucher;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GeneratePhysicalVoucherController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly UuidValidator $uuidValidator,
    ) {
    }

    #[Route('/api/vouchers/{voucherId}/generate-physical', name: 'api_voucher_generate_physical', methods: ['POST'])]
    #[OA\Post(
        path: '/api/vouchers/{voucherId}/generate-physical',
        description: 'Generates a physical voucher image.',
        summary: 'Generate physical voucher',
        security: [['Bearer' => []]],
        tags: ['Voucher'],
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
        response: Response::HTTP_OK,
        description: 'Physical voucher generated successfully.',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'imageUrl',
                    type: 'string',
                    example: 'data:image/png;base64,...',
                ),
            ],
        ),
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: 'Voucher not found.',
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: 'Invalid request or voucher not claimed by user.',
    )]
    public function __invoke(string $voucherId, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$this->uuidValidator->isValid($voucherId)) {
            throw new InvalidRequestParameterException('voucherId', sprintf('Invalid Voucher "%s".', $voucherId));
        }

        $imageUrl = $this->queryBus->ask(
            new GeneratePhysicalVoucher(
                $voucherId,
                $user->getId(),
            ),
        );

        return new JsonResponse(['imageUrl' => $imageUrl]);
    }
}
