<?php

declare(strict_types=1);

namespace App\Tests\Auth\UI\Console;

use App\Auth\Application\EmailBreach\EmailBreachChecker;
use App\Auth\Application\EmailBreach\EmailBreachCheckResult;
use App\Tests\ApiWebTestCase;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;

final class ProcessEmailBreachChecksCommandTest extends ApiWebTestCase
{
    public function testCreatesNotificationWhenOptedInUserEmailIsBreached(): void
    {
        $client = self::createClient();
        $email = 'breached-user@example.com';
        self::registerVerifyAndGetUserId($client, $email, 'securePassword123');
        self::useFakeEmailBreachChecker();
        $user = self::getUserRecord($email);
        $user->setEmailBreachCheckEnabled(true);

        self::getEntityManager()->flush();

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Email breach checks processed: 1', $tester->getDisplay());

        $updatedUser = self::getUserRecord($email);

        self::assertInstanceOf(DateTimeImmutable::class, $updatedUser->getEmailBreachCheckedAt());
        self::assertInstanceOf(DateTimeImmutable::class, $updatedUser->getEmailBreachedAt());
        self::assertSame(3, $updatedUser->getEmailBreachCount());
        self::assertSame(
            1,
            self::countNotificationsByUserIdAndType($updatedUser->getId(), 'email_breach_detected'),
        );
    }

    public function testDoesNotCreateNotificationWhenOptedInUserEmailIsNotBreached(): void
    {
        $client = self::createClient();

        self::useFakeEmailBreachChecker();

        $email = 'safe-user@example.com';
        self::registerVerifyAndGetUserId($client, $email, 'securePassword123');

        $user = self::getUserRecord($email);
        $user->setEmailBreachCheckEnabled(true);

        self::getEntityManager()->flush();

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Email breach checks processed: 1', $tester->getDisplay());

        $updatedUser = self::getUserRecord($email);

        self::assertInstanceOf(DateTimeImmutable::class, $updatedUser->getEmailBreachCheckedAt());
        self::assertNull($updatedUser->getEmailBreachedAt());
        self::assertSame(0, $updatedUser->getEmailBreachCount());
        self::assertSame(
            0,
            self::countNotificationsByUserIdAndType($updatedUser->getId(), 'email_breach_detected'),
        );
    }

    public function testDoesNotCheckUserWhenEmailBreachCheckIsDisabled(): void
    {
        $client = self::createClient();

        self::useFakeEmailBreachChecker();

        $email = 'breached-disabled-user@example.com';
        self::registerVerifyAndGetUserId($client, $email, 'securePassword123');

        $user = self::getUserRecord($email);
        $user->setEmailBreachCheckEnabled(false);

        self::getEntityManager()->flush();

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Email breach checks processed: 0', $tester->getDisplay());

        $updatedUser = self::getUserRecord($email);

        self::assertNull($updatedUser->getEmailBreachCheckedAt());
        self::assertNull($updatedUser->getEmailBreachedAt());
        self::assertSame(0, $updatedUser->getEmailBreachCount());
        self::assertSame(
            0,
            self::countNotificationsByUserIdAndType($updatedUser->getId(), 'email_breach_detected'),
        );
    }

    public function testClearsPreviousBreachStateWhenEmailIsNoLongerBreached(): void
    {
        $client = self::createClient();

        self::useFakeEmailBreachChecker();

        $email = 'safe-previously-breached-user@example.com';
        self::registerVerifyAndGetUserId($client, $email, 'securePassword123');

        $user = self::getUserRecord($email);
        $user
            ->setEmailBreachCheckEnabled(true)
            ->setEmailBreachCheckedAt(new DateTimeImmutable('-8 days'))
            ->setEmailBreachedAt(new DateTimeImmutable('-8 days'))
            ->setEmailBreachCount(2);

        self::getEntityManager()->flush();

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Email breach checks processed: 1', $tester->getDisplay());

        $updatedUser = self::getUserRecord($email);

        self::assertInstanceOf(DateTimeImmutable::class, $updatedUser->getEmailBreachCheckedAt());
        self::assertNull($updatedUser->getEmailBreachedAt());
        self::assertSame(0, $updatedUser->getEmailBreachCount());
    }

    public function testDoesNotRecheckUserCheckedLessThanSevenDaysAgo(): void
    {
        $client = self::createClient();

        self::useFakeEmailBreachChecker();

        $email = 'breached-recently-checked-user@example.com';
        self::registerVerifyAndGetUserId($client, $email, 'securePassword123');

        $checkedAt = new DateTimeImmutable('-1 day');
        $user = self::getUserRecord($email);
        $user
            ->setEmailBreachCheckEnabled(true)
            ->setEmailBreachCheckedAt($checkedAt);

        self::getEntityManager()->flush();

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Email breach checks processed: 0', $tester->getDisplay());

        $updatedUser = self::getUserRecord($email);

        self::assertSame($checkedAt->getTimestamp(), $updatedUser->getEmailBreachCheckedAt()?->getTimestamp());
        self::assertNull($updatedUser->getEmailBreachedAt());
        self::assertSame(0, $updatedUser->getEmailBreachCount());
    }

    public function testChecksAtMostTenUsersPerRun(): void
    {
        $client = self::createClient();
        $emails = [];

        for ($i = 1; $i <= 11; ++$i) {
            $email = sprintf('breached-limit-user-%02d@example.com', $i);
            self::registerVerifyAndGetUserId($client, $email, 'securePassword123');
            $emails[] = $email;
        }

        self::useFakeEmailBreachChecker();

        foreach ($emails as $email) {
            $user = self::getUserRecord($email);
            $user->setEmailBreachCheckEnabled(true);
        }

        self::getEntityManager()->flush();
        self::getEntityManager()->clear();

        $tester = self::executeCommand();

        self::assertSame(0, $tester->getStatusCode());
        self::assertStringContainsString('Email breach checks processed: 10', $tester->getDisplay());

        $checkedUsers = 0;

        for ($i = 1; $i <= 11; ++$i) {
            $email = sprintf('breached-limit-user-%02d@example.com', $i);
            $user = self::getUserRecord($email);

            if ($user->getEmailBreachCheckedAt() instanceof DateTimeImmutable) {
                ++$checkedUsers;
            }
        }

        self::assertSame(10, $checkedUsers);
    }

    private static function executeCommand(): CommandTester
    {
        $kernel = self::$kernel;
        self::assertInstanceOf(KernelInterface::class, $kernel);

        $application = new Application($kernel);
        $command = $application->find('app:email-breach-checks:process');
        $tester = new CommandTester($command);

        $tester->execute([]);

        return $tester;
    }

    private static function useFakeEmailBreachChecker(): void
    {
        self::getContainer()->set(EmailBreachChecker::class, new class implements EmailBreachChecker {
            public function check(string $email): EmailBreachCheckResult
            {
                if (str_contains($email, 'breached')) {
                    return new EmailBreachCheckResult(true, 3);
                }

                return new EmailBreachCheckResult(false, 0);
            }
        });
    }
}
