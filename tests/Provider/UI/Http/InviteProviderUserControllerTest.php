<?php

declare(strict_types=1);

namespace App\Tests\Provider\UI\Http;

use App\Provider\Domain\Invitation\ProviderInvitationStatus;
use App\Provider\Domain\Role\ProviderUserRole;
use App\Provider\Domain\Status\ProviderStatus;
use App\Provider\Domain\Status\ProviderUserStatus;
use App\Tests\ApiWebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class InviteProviderUserControllerTest extends ApiWebTestCase
{
    public function testInviteProviderUserWithoutAuthenticationReturnsUnauthorized(): void
    {
        $client = self::createClient();
        $providerId = self::getUuidCreator()->create();
        $client->request(
            'POST',
            sprintf('/api/provider/%s/invite', $providerId),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            self::json([
                'email' => 'member@example.com',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testInviteProviderUserWithInvalidJsonReturnsBadRequest(): void
    {
        $client = self::createClient();
        $adminEmail = 'invite-invalid-json-admin@example.com';
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            $adminEmail,
            'securePassword123',
        );
        $token = self::login($client, $adminEmail, 'securePassword123');
        $providerId = self::createProviderRecord('Invite Invalid Json Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'POST',
            sprintf('/api/provider/%s/invite', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            '{invalid-json',
        );

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertSame(
            [
                'message' => 'Invalid JSON payload.',
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testInviteProviderUserWithMissingEmailReturnsValidationError(): void
    {
        $client = self::createClient();
        $adminEmail = 'invite-missing-email-admin@example.com';
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            $adminEmail,
            'securePassword123',
        );
        $token = self::login($client, $adminEmail, 'securePassword123');
        $providerId = self::createProviderRecord('Invite Missing Email Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'POST',
            sprintf('/api/provider/%s/invite', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'email',
                        'message' => 'Email is required.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testInviteProviderUserWithInvalidEmailReturnsValidationError(): void
    {
        $client = self::createClient();
        $adminEmail = 'invite-invalid-email-admin@example.com';
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            $adminEmail,
            'securePassword123',
        );
        $token = self::login($client, $adminEmail, 'securePassword123');
        $providerId = self::createProviderRecord('Invite Invalid Email Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'POST',
            sprintf('/api/provider/%s/invite', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'email' => 'invalid-email',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame(
            [
                'message' => 'Validation failed.',
                'errors' => [
                    [
                        'field' => 'email',
                        'message' => 'Email must be valid.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testInviteProviderUserAsNonAdminReturnsForbidden(): void
    {
        $client = self::createClient();
        $memberEmail = 'invite-non-admin@example.com';
        $memberUserId = self::registerVerifyAndGetUserId(
            $client,
            $memberEmail,
            'securePassword123',
        );
        $token = self::login($client, $memberEmail, 'securePassword123');
        $providerId = self::createProviderRecord('Invite Non Admin Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $memberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );

        $client->request(
            'POST',
            sprintf('/api/provider/%s/invite', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'email' => 'invited-by-non-admin@example.com',
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        self::assertSame(
            [
                'message' => 'Forbidden.',
                'errors' => [
                    [
                        'field' => 'provider',
                        'message' => 'You are not allowed to perform this action.',
                    ],
                ],
            ],
            self::getJsonResponse($client->getResponse()->getContent()),
        );
    }

    public function testInviteProviderUserSuccessfullyCreatesPendingInvitation(): void
    {
        $client = self::createClient();
        $adminEmail = 'invite-success-admin@example.com';
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            $adminEmail,
            'securePassword123',
        );
        $token = self::login($client, $adminEmail, 'securePassword123');
        $providerId = self::createProviderRecord('Invite Success Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $invitedEmail = 'new-provider-member@example.com';
        $client->request(
            'POST',
            sprintf('/api/provider/%s/invite', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'email' => $invitedEmail,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $invitation = self::getProviderInvitationByEmail($providerId, $invitedEmail);

        self::assertSame($providerId, $invitation->getProviderId());
        self::assertSame($invitedEmail, $invitation->getEmail());
        self::assertSame(ProviderUserRole::Member->value, $invitation->getRole());
        self::assertSame(ProviderInvitationStatus::Pending->value, $invitation->getStatus());
        self::assertSame($adminUserId, $invitation->getInvitedByUserId());
        self::assertNull($invitation->getAcceptedUserId());
        self::assertNull($invitation->getAcceptedAt());
    }

    public function testInviteProviderUserWithExistingPendingInvitationDoesNotCreateDuplicate(): void
    {
        $client = self::createClient();
        $adminEmail = 'invite-existing-pending-admin@example.com';
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            $adminEmail,
            'securePassword123',
        );
        $token = self::login($client, $adminEmail, 'securePassword123');
        $providerId = self::createProviderRecord('Invite Existing Pending Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $invitedEmail = 'existing-pending-invited@example.com';
        self::createProviderInvitationRecord(
            providerId: $providerId,
            email: $invitedEmail,
            slug: 'existing-pending-invitation-slug',
            status: ProviderInvitationStatus::Pending->value,
            invitedByUserId: $adminUserId,
        );
        $client->request(
            'POST',
            sprintf('/api/provider/%s/invite', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'email' => $invitedEmail,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertSame(1, self::countProviderInvitationsByEmail($providerId, $invitedEmail));
    }

    public function testInviteProviderUserWhenInvitedUserIsAlreadyActiveMemberDoesNotCreateInvitation(): void
    {
        $client = self::createClient();
        $adminEmail = 'invite-existing-member-admin@example.com';
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            $adminEmail,
            'securePassword123',
        );
        $token = self::login($client, $adminEmail, 'securePassword123');
        $memberEmail = 'already-active-member@example.com';
        $memberUserId = self::registerVerifyAndGetUserId(
            $client,
            $memberEmail,
            'securePassword123',
        );
        $providerId = self::createProviderRecord('Invite Existing Member Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $memberUserId,
            role: ProviderUserRole::Member->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'POST',
            sprintf('/api/provider/%s/invite', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'email' => $memberEmail,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertSame(0, self::countProviderInvitationsByEmail($providerId, $memberEmail));
    }

    public function testInviteProviderUserInvitingSelfDoesNotCreateInvitation(): void
    {
        $client = self::createClient();
        $adminEmail = 'invite-self-admin@example.com';
        $adminUserId = self::registerVerifyAndGetUserId(
            $client,
            $adminEmail,
            'securePassword123',
        );
        $token = self::login($client, $adminEmail, 'securePassword123');
        $providerId = self::createProviderRecord('Invite Self Provider', ProviderStatus::Active->value);
        self::createProviderUserRecord(
            providerId: $providerId,
            userId: $adminUserId,
            role: ProviderUserRole::Admin->value,
            status: ProviderUserStatus::Active->value,
        );
        $client->request(
            'POST',
            sprintf('/api/provider/%s/invite', $providerId),
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => sprintf('Bearer %s', $token),
            ],
            self::json([
                'email' => $adminEmail,
            ]),
        );

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertSame(0, self::countProviderInvitationsByEmail($providerId, $adminEmail));
    }
}
