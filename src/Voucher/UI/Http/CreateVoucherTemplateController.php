<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\CommandBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Shared\Domain\Id\UuidCreator;
use App\Shared\UI\Http\JsonDtoFactory;
use App\Voucher\Application\Command\CreateVoucherTemplate;
use App\Voucher\UI\Http\Request\CreateVoucherTemplateRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CreateVoucherTemplateController extends AbstractController
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly JsonDtoFactory $jsonDtoFactory,
        private readonly UuidCreator $uuidCreator,
    ) {
    }

    #[Route('/api/providers/{providerId}/voucher-templates', name: 'api_provider_voucher_template_create', methods: ['POST'])]
    public function __invoke(string $providerId, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var CreateVoucherTemplateRequest $dto */
        $dto = $this->jsonDtoFactory->create($request, CreateVoucherTemplateRequest::class);
        $voucherTemplateId = $this->uuidCreator->create();

        $this->commandBus->dispatch(new CreateVoucherTemplate(
            $voucherTemplateId,
            $providerId,
            $user->getId(),
            $dto->name,
            $dto->type,
            $dto->title,
            $dto->description,
            $dto->htmlTemplate,
        ));

        return new JsonResponse(['id' => $voucherTemplateId], Response::HTTP_CREATED);
    }
}
