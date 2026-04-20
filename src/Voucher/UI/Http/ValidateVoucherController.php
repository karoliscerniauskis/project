<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\UI\Http\JsonDtoFactory;
use App\Voucher\Application\Query\ValidateVoucher;
use App\Voucher\Domain\View\VoucherValidationView;
use App\Voucher\UI\Http\Request\ValidateVoucherRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ValidateVoucherController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
    ) {
    }

    #[Route('/api/providers/{providerId}/vouchers/validate', name: 'api_provider_vouchers_validate', methods: ['POST'])]
    public function __invoke(string $providerId, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var ValidateVoucherRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, ValidateVoucherRequest::class);

        /** @var VoucherValidationView $voucherValidationView */
        $voucherValidationView = $this->queryBus->ask(
            new ValidateVoucher($providerId, $dto->code),
        );

        return new JsonResponse(['data' => $voucherValidationView->toArray()]);
    }
}
