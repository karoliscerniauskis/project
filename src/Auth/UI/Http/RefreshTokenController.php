<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Infrastructure\Doctrine\Entity\RefreshToken;
use App\Auth\Infrastructure\Doctrine\Entity\UserRecord;
use App\Auth\Infrastructure\Security\SecurityUser;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class RefreshTokenController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private JWTTokenManagerInterface $jwtTokenManager,
    ) {
    }

    #[Route('/api/auth/token/refresh', name: 'api_auth_token_refresh', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload) || !isset($payload['refresh_token']) || !is_string($payload['refresh_token'])) {
            return new JsonResponse([
                'message' => 'Missing refresh token.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $refreshTokenValue = $payload['refresh_token'];

        /** @var RefreshToken|null $refreshToken */
        $refreshToken = $this->entityManager
            ->getRepository(RefreshToken::class)
            ->findOneBy(['refreshToken' => $refreshTokenValue]);

        if (!$refreshToken instanceof RefreshToken) {
            return new JsonResponse([
                'message' => 'Invalid refresh token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($refreshToken->getValid() !== null && $refreshToken->getValid() < new DateTimeImmutable()) {
            return new JsonResponse([
                'message' => 'Refresh token expired.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $email = $refreshToken->getUsername();

        if (!is_string($email) || $email === '') {
            return new JsonResponse([
                'message' => 'Invalid refresh token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $userRecord = $this->entityManager
            ->getRepository(UserRecord::class)
            ->findOneBy(['email' => $email]);

        if (!$userRecord instanceof UserRecord) {
            return new JsonResponse([
                'message' => 'User not found.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $securityUser = new SecurityUser(
            $userRecord->getEmail(),
            $userRecord->getId(),
            $userRecord->getHashedPassword(),
            $userRecord->getRoles(),
            $userRecord->getEmailVerifiedAt(),
        );

        $newToken = $this->jwtTokenManager->create($securityUser);

        return new JsonResponse([
            'token' => $newToken,
        ]);
    }
}
