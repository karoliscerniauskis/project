<?php

declare(strict_types=1);

namespace App\Auth\Application\EmailBreach;

use App\Auth\Domain\Repository\UserRepository;
use App\Shared\Application\Email\EmailSender;
use App\Shared\Application\Notification\NotificationSender;
use App\Shared\Application\Persistence\Flusher;
use App\Shared\Domain\Clock\Clock;

final readonly class EmailBreachCheckProcessor
{
    private const string NOTIFICATION_TYPE = 'email_breach_detected';
    private const int CHECK_LIMIT = 10;

    public function __construct(
        private UserRepository $userRepository,
        private EmailBreachChecker $emailBreachChecker,
        private NotificationSender $notificationSender,
        private Clock $clock,
        private Flusher $flusher,
        private EmailSender $emailSender,
        private string $emailFrom,
    ) {
    }

    public function process(): int
    {
        $now = $this->clock->now();
        // Check only users who have never been checked or were checked more than 7 days ago.
        // The command can run more often, but each user is rechecked at most once every 7 days.
        $recheckThreshold = $now->modify('-7 days');
        $checked = 0;

        foreach ($this->userRepository->findEmailBreachCheckCandidates($recheckThreshold, self::CHECK_LIMIT) as $user) {
            $result = $this->emailBreachChecker->check($user->getEmail());

            $user->markEmailBreachCheckCompleted(
                $now,
                $result->isBreached(),
                $result->getBreachCount(),
            );

            $this->userRepository->save($user);
            $this->flusher->flush();

            if ($result->isBreached()) {
                $message = 'Your email address was found in public data breaches. To protect your account and vouchers, please review your security settings and consider changing your password.';
                $this->notificationSender->send(
                    $user->getId(),
                    self::NOTIFICATION_TYPE,
                    'Your email may be at risk',
                    $message,
                    [
                        'breachCount' => $result->getBreachCount(),
                    ],
                );

                $this->emailSender->send(
                    $this->emailFrom,
                    $user->getEmail(),
                    'Your email may be at risk',
                    $message,
                );
            }

            ++$checked;
        }

        return $checked;
    }
}
