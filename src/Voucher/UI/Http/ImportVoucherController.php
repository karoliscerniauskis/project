<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Voucher\Application\Command\ImportVoucher;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ImportVoucherController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
    }

    #[Route('/api/vouchers/import', name: 'api_voucher_import', methods: ['POST'])]
    #[OA\Post(
        path: '/api/vouchers/import',
        description: 'Imports a voucher by code. The voucher will be assigned to the authenticated user and automatically claimed.',
        summary: 'Import voucher',
        security: [['Bearer' => []]],
        tags: ['Voucher'],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['code'],
            properties: [
                new OA\Property(
                    property: 'code',
                    type: 'string',
                    example: 'SUMMER2024',
                ),
            ],
        ),
    )]
    #[OA\Response(
        response: Response::HTTP_NO_CONTENT,
        description: 'Voucher imported successfully.',
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
        description: 'Voucher cannot be imported (not active, etc.).',
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(
                ['message' => 'Invalid request body'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $code = $data['code'] ?? null;

        if (!is_string($code) || $code === '') {
            return new JsonResponse(
                ['message' => 'Code is required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->commandBus->dispatch(
            new ImportVoucher(
                $code,
                $user->getId(),
                $user->getUserIdentifier(),
            ),
        );

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
