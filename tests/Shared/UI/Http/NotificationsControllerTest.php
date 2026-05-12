<?php

declare(strict_types=1);

namespace App\Tests\Shared\UI\Http;

use App\Shared\Infrastructure\Doctrine\Notification\Entity\NotificationRecord;
use App\Tests\ApiWebTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

final class NotificationsControllerTest extends ApiWebTestCase
{
    public function testGetMyNotificationsWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/me/notifications');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetUnreadNotificationsCountWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();

        $client->request('GET', '/api/me/notifications/unread-count');

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testMarkNotificationAsReadWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $notificationId = self::getUuidCreator()->create();

        $client->request(
            'POST',
            sprintf('/api/me/notifications/%s/read', $notificationId),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetMyNotificationsReturnsOnlyAuthenticatedUserNotifications(): void
    {
        $client = self::createClient();
        $email = 'notifications-owner@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $otherUserId = self::registerVerifyAndGetUserId(
            $client,
            'notifications-other-user@example.com',
            'securePassword123',
        );

        $newestNotificationId = self::createNotificationRecord(
            userId: $userId,
            type: 'voucher.created',
            title: 'Newest notification',
            message: 'Newest notification message',
            payload: [
                'voucherId' => 'voucher-newest',
            ],
            createdAt: new DateTimeImmutable('2020-01-02 00:00:00'),
        );
        $oldestNotificationId = self::createNotificationRecord(
            userId: $userId,
            type: 'provider.approved',
            title: 'Oldest notification',
            message: 'Oldest notification message',
            payload: [
                'providerId' => 'provider-oldest',
            ],
            createdAt: new DateTimeImmutable('2020-01-01 00:00:00'),
        );
        self::createNotificationRecord(
            userId: $otherUserId,
            type: 'voucher.created',
            title: 'Other user notification',
            message: 'Other user notification message',
            payload: [],
            createdAt: new DateTimeImmutable('2020-01-03 00:00:00'),
        );

        $client->request(
            'GET',
            '/api/me/notifications',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = self::getJsonResponse($client->getResponse()->getContent());

        self::assertArrayHasKey('data', $response);
        self::assertIsArray($response['data']);
        self::assertCount(2, $response['data']);

        $newestNotification = $response['data'][0];
        $oldestNotification = $response['data'][1];

        self::assertIsArray($newestNotification);
        self::assertIsArray($oldestNotification);

        self::assertSame($newestNotificationId, $newestNotification['id']);
        self::assertSame('voucher.created', $newestNotification['type']);
        self::assertSame('Newest notification', $newestNotification['title']);
        self::assertSame('Newest notification message', $newestNotification['message']);
        self::assertSame(['voucherId' => 'voucher-newest'], $newestNotification['payload']);
        self::assertArrayHasKey('readAt', $oldestNotification);
        self::assertNull($oldestNotification['readAt']);
        self::assertArrayHasKey('createdAt', $oldestNotification);
        self::assertIsString($oldestNotification['createdAt']);

        self::assertSame($oldestNotificationId, $oldestNotification['id']);
        self::assertSame('provider.approved', $oldestNotification['type']);
        self::assertSame('Oldest notification', $oldestNotification['title']);
        self::assertSame('Oldest notification message', $oldestNotification['message']);
        self::assertSame(['providerId' => 'provider-oldest'], $oldestNotification['payload']);
        self::assertArrayHasKey('readAt', $oldestNotification);
        self::assertNull($oldestNotification['readAt']);
        self::assertArrayHasKey('createdAt', $oldestNotification);
        self::assertIsString($oldestNotification['createdAt']);
    }

    public function testGetMyNotificationsReturnsEmptyArrayWhenUserHasNoNotifications(): void
    {
        $client = self::createClient();
        $token = self::registerVerifyAndLoginUser(
            $client,
            'notifications-empty@example.com',
            'securePassword123',
        );

        $client->request(
            'GET',
            '/api/me/notifications',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            [
                'data' => [],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testGetUnreadNotificationsCountReturnsOnlyUnreadNotificationsForAuthenticatedUser(): void
    {
        $client = self::createClient();
        $email = 'notifications-count-owner@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $otherUserId = self::registerVerifyAndGetUserId(
            $client,
            'notifications-count-other-user@example.com',
            'securePassword123',
        );

        self::createNotificationRecord(
            userId: $userId,
            type: 'unread.one',
            title: 'Unread one',
            message: 'Unread one message',
            payload: [],
        );
        self::createNotificationRecord(
            userId: $userId,
            type: 'unread.two',
            title: 'Unread two',
            message: 'Unread two message',
            payload: [],
        );
        $readNotificationId = self::createNotificationRecord(
            userId: $userId,
            type: 'read.one',
            title: 'Read one',
            message: 'Read one message',
            payload: [],
        );
        self::markNotificationRecordAsRead($readNotificationId);

        self::createNotificationRecord(
            userId: $otherUserId,
            type: 'other.unread',
            title: 'Other unread',
            message: 'Other unread message',
            payload: [],
        );

        $client->request(
            'GET',
            '/api/me/notifications/unread-count',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            [
                'data' => [
                    'count' => 2,
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testMarkNotificationAsReadReturnsNotFoundForOtherUserNotification(): void
    {
        $client = self::createClient();
        $email = 'notifications-read-owner@example.com';
        self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');
        $otherUserId = self::registerVerifyAndGetUserId(
            $client,
            'notifications-read-other-user@example.com',
            'securePassword123',
        );

        $notificationId = self::createNotificationRecord(
            userId: $otherUserId,
            type: 'other.notification',
            title: 'Other notification',
            message: 'Other notification message',
            payload: [],
        );

        $client->request(
            'POST',
            sprintf('/api/me/notifications/%s/read', $notificationId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $notification = self::getNotificationRecord($notificationId);

        self::assertFalse($notification->isRead());
    }

    public function testMarkNotificationAsReadMarksNotificationAsReadAndUnreadCountDecreases(): void
    {
        $client = self::createClient();
        $email = 'notifications-mark-read@example.com';
        $userId = self::registerVerifyAndGetUserId(
            $client,
            $email,
            'securePassword123',
        );
        $token = self::login($client, $email, 'securePassword123');

        $notificationId = self::createNotificationRecord(
            userId: $userId,
            type: 'mark.read',
            title: 'Mark read',
            message: 'Mark read message',
            payload: [
                'key' => 'value',
            ],
        );

        $client->request(
            'GET',
            '/api/me/notifications/unread-count',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            [
                'data' => [
                    'count' => 1,
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );

        $client->request(
            'POST',
            sprintf('/api/me/notifications/%s/read', $notificationId),
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);

        $notification = self::getNotificationRecord($notificationId);

        self::assertTrue($notification->isRead());

        $client->request(
            'GET',
            '/api/me/notifications/unread-count',
            [],
            [],
            [
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
        );

        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        self::assertSame(
            [
                'data' => [
                    'count' => 0,
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function createNotificationRecord(
        string $userId,
        string $type,
        string $title,
        string $message,
        array $payload,
        ?DateTimeImmutable $createdAt = null,
    ): string {
        $notificationId = self::getUuidCreator()->create();

        $notification = new NotificationRecord(
            $notificationId,
            $userId,
            $type,
            $title,
            $message,
            $payload,
            $createdAt ?? new DateTimeImmutable(),
        );

        $entityManager = self::getEntityManager();
        $entityManager->persist($notification);
        $entityManager->flush();

        return $notificationId;
    }

    private static function markNotificationRecordAsRead(string $notificationId): void
    {
        $notification = self::getNotificationRecord($notificationId);

        $notification->markAsRead(new DateTimeImmutable());

        self::getEntityManager()->flush();
    }

    private static function getNotificationRecord(string $notificationId): NotificationRecord
    {
        $notification = self::getEntityManager()
            ->getRepository(NotificationRecord::class)
            ->find($notificationId);

        self::assertInstanceOf(NotificationRecord::class, $notification);

        return $notification;
    }
}
