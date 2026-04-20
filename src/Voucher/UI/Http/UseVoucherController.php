<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\UI\Http\JsonDtoFactory;
use App\Voucher\Application\Command\UseVoucher;
use App\Voucher\UI\Http\Request\UseVoucherRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UseVoucherController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
    ) {
    }

    #[Route('/api/providers/{providerId}/vouchers/use', name: 'api_provider_vouchers_use', methods: ['POST'])]
    public function __invoke(string $providerId, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var UseVoucherRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, UseVoucherRequest::class);

        $this->commandBus->dispatch(
            new UseVoucher($providerId, $dto->code),
        );

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
