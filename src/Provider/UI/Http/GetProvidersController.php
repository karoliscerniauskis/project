<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Auth\Infrastructure\Security\SecurityUser;
use App\Provider\Application\Query\GetProviders;
use App\Provider\Domain\View\ProvidersView;
use App\Shared\Application\Bus\QueryBus;
use App\Shared\Domain\Id\UserId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetProvidersController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/api/providers', name: 'api_providers_list', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof SecurityUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        /** @var ProvidersView $providersView */
        $providersView = $this->queryBus->ask(
            new GetProviders(UserId::fromString($user->getId())),
        );

        $data = array_map(static function ($provider): array {
            return [
                'id' => $provider->getId(),
                'name' => $provider->getName(),
                'status' => $provider->getStatus(),
            ];
        }, $providersView->getProviders());

        return new JsonResponse([
            'data' => $data,
        ]);
    }
}
