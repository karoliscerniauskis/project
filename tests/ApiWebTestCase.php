<?php

declare(strict_types=1);

namespace App\Tests;

use App\Auth\Infrastructure\Doctrine\Entity\UserRecord;
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

    protected static function getUserRecord(string $email): UserRecord
    {
        $userRecord = self::getEntityManager()
            ->getRepository(UserRecord::class)
            ->findOneBy(['email' => $email]);

        self::assertInstanceOf(UserRecord::class, $userRecord);

        return $userRecord;
    }
}
