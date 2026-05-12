<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Shared\Application\Bus\QueryBus;
use App\Shared\Application\Security\AuthenticatedUser;
use App\Voucher\Application\Query\GetMyVouchers;
use App\Voucher\Domain\View\PaginatedMyVouchersView;
use App\Voucher\UI\Http\OpenApi\MyVouchersResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetMyVouchersController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
    ) {
    }

    #[Route('/api/me/vouchers', name: 'api_me_vouchers_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/me/vouchers',
        description: 'Returns vouchers owned by the authenticated user.',
        summary: 'List my vouchers',
        security: [['Bearer' => []]],
        tags: ['Voucher'],
    )]
    #[OA\Parameter(
        name: 'code',
        description: 'Filter vouchers by code (partial match).',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Page number.',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 1),
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Items per page.',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 20),
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: 'Vouchers returned successfully.',
        content: new OA\JsonContent(ref: new Model(type: MyVouchersResponse::class)),
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: 'Authentication is required.',
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return new JsonResponse(status: Response::HTTP_UNAUTHORIZED);
        }

        $codeFilter = $request->query->get('code');
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = max(1, min(100, (int) $request->query->get('perPage', 20)));

        /** @var PaginatedMyVouchersView $paginatedView */
        $paginatedView = $this->queryBus->ask(
            new GetMyVouchers(
                $user->getUserIdentifier(),
                $user->getId(),
                $codeFilter,
                $page,
                $perPage
            ),
        );

        return new JsonResponse($paginatedView->toArray());
    }
}
