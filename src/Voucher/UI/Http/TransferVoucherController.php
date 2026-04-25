<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\UI\Http\JsonDtoFactory;
use App\Voucher\Application\Command\TransferVoucher;
use App\Voucher\UI\Http\Request\TransferVoucherRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TransferVoucherController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
    ) {
    }

    #[Route('/api/vouchers/{voucherId}/transfer', name: 'api_voucher_transfer', methods: ['POST'])]
    public function __invoke(string $voucherId, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var TransferVoucherRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, TransferVoucherRequest::class);
        $this->commandBus->dispatch(
            new TransferVoucher(
                $voucherId,
                $user->getUserIdentifier(),
                $dto->recipientEmail,
            ),
        );

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
