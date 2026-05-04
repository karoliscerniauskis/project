<?php

declare(strict_types=1);

namespace App\Tests;

use App\Auth\Domain\Security\UserPasswordHasher;
use App\Auth\Infrastructure\Doctrine\Entity\UserRecord;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderInvitationRecord;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderRecord;
use App\Provider\Infrastructure\Doctrine\Entity\ProviderUserRecord;
use App\Shared\Domain\Id\ProviderId;
use App\Shared\Domain\Id\ProviderInvitationId;
use App\Shared\Domain\Id\UuidCreator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiWebTestCase extends WebTestCase
{
    /**
     * @param array<string, mixed> $payload
     */
    protected static function json(array $payload): string
    {
        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        return $json;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function getJsonResponse(string|false $content): array
    {
        self::assertIsString($content);

        /** @var array<string, mixed> $response */
        $response = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $response;
    }

    protected static function registerVerifyAndLoginUser(KernelBrowser $client, string $email, string $password): string
    {
        $client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'email' => $email,
                'password' => $password,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $emailVerificationSlug = self::getEmailVerificationSlug($email);
        $client->request(
            'GET',
            sprintf('/api/auth/verify-email/%s', $emailVerificationSlug),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'email' => $email,
                'password' => $password,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = self::getJsonResponse($client->getResponse()->getContent());

        self::assertIsString($response['token']);

        return $response['token'];
    }

    protected static function getEmailVerificationSlug(string $email): string
    {
        $entityManager = self::getEntityManager();
        $userRecord = $entityManager
            ->getRepository(UserRecord::class)
            ->findOneBy(['email' => $email]);

        self::assertInstanceOf(UserRecord::class, $userRecord);

        $emailVerificationSlug = $userRecord->getEmailVerificationSlug();

        self::assertIsString($emailVerificationSlug);

        return $emailVerificationSlug;
    }

    protected static function getEntityManager(): EntityManagerInterface
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);

        return $entityManager;
    }

    protected static function getUuidCreator(): UuidCreator
    {
        $uuidCreator = self::getContainer()->get(UuidCreator::class);

        self::assertInstanceOf(UuidCreator::class, $uuidCreator);

        return $uuidCreator;
    }

    protected static function getUserRecord(string $email): UserRecord
    {
        $userRecord = self::getEntityManager()
            ->getRepository(UserRecord::class)
            ->findOneBy(['email' => $email]);

        self::assertInstanceOf(UserRecord::class, $userRecord);

        return $userRecord;
    }

    protected static function getProviderUser(string $providerId, string $userId): ?ProviderUserRecord
    {
        $providerUser = self::getEntityManager()
            ->getRepository(ProviderUserRecord::class)
            ->findOneBy([
                'providerId' => $providerId,
                'userId' => $userId,
            ]);

        return $providerUser instanceof ProviderUserRecord ? $providerUser : null;
    }

    protected static function registerVerifyAndGetUserId(
        KernelBrowser $client,
        string $email,
        string $password,
    ): string {
        self::registerVerifyAndLoginUser($client, $email, $password);
        $userRecord = self::getUserRecord($email);

        return $userRecord->getId();
    }

    protected static function login(
        KernelBrowser $client,
        string $email,
        string $password,
    ): string {
        $client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'email' => $email,
                'password' => $password,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = self::getJsonResponse($client->getResponse()->getContent());

        self::assertIsString($response['token']);

        return $response['token'];
    }

    protected static function createProviderRecord(string $name, string $status): string
    {
        $providerId = ProviderId::fromString(self::getUuidCreator()->create())->toString();
        $provider = new ProviderRecord(
            $providerId,
            $name,
            $status,
        );
        $entityManager = self::getEntityManager();
        $entityManager->persist($provider);
        $entityManager->flush();

        return $providerId;
    }

    protected static function createProviderInvitationRecord(
        string $providerId,
        string $email,
        string $slug,
        string $status,
        string $invitedByUserId,
        ?string $acceptedUserId = null,
        ?DateTimeImmutable $acceptedAt = null,
    ): void {
        $invitation = new ProviderInvitationRecord(
            ProviderInvitationId::fromString(self::getUuidCreator()->create())->toString(),
            $providerId,
            $email,
            ProviderUserRole::Member->value,
            $slug,
            $status,
            $invitedByUserId,
            $acceptedUserId,
            new DateTimeImmutable('-1 hour'),
            $acceptedAt,
            new DateTimeImmutable('+1 day'),
        );
        $entityManager = self::getEntityManager();
        $entityManager->persist($invitation);
        $entityManager->flush();
    }

    protected static function getProviderInvitationBySlug(string $slug): ProviderInvitationRecord
    {
        $invitation = self::getEntityManager()
            ->getRepository(ProviderInvitationRecord::class)
            ->findOneBy(['slug' => $slug]);

        self::assertInstanceOf(ProviderInvitationRecord::class, $invitation);

        return $invitation;
    }

    protected static function createAdminUserAndLogin(
        KernelBrowser $client,
        string $email,
        string $password,
    ): string {
        $passwordHasher = self::getContainer()->get(UserPasswordHasher::class);

        self::assertInstanceOf(UserPasswordHasher::class, $passwordHasher);

        $user = new UserRecord(
            self::getUuidCreator()->create(),
            $email,
            null,
            $passwordHasher->hashPassword($password),
            ['ROLE_ADMIN', 'ROLE_USER'],
            null,
            new DateTimeImmutable(),
        );
        $entityManager = self::getEntityManager();
        $entityManager->persist($user);
        $entityManager->flush();
        $client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'email' => $email,
                'password' => $password,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = self::getJsonResponse($client->getResponse()->getContent());

        self::assertIsString($response['token']);

        return $response['token'];
    }

    protected static function createProviderUserRecord(
        string $providerId,
        string $userId,
        string $role,
        string $status,
    ): void {
        $providerUser = new ProviderUserRecord(
            self::getUuidCreator()->create(),
            $providerId,
            $userId,
            $role,
            $status,
        );
        $entityManager = self::getEntityManager();
        $entityManager->persist($providerUser);
        $entityManager->flush();
    }
}
