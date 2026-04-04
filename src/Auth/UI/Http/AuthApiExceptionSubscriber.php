<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Exception\UserEmailMustBeUnique;
use App\Auth\Application\Exception\UserEmailMustBeVerified;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class AuthApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        if ($event->getThrowable()->getPrevious() instanceof UserEmailMustBeUnique) {
            $event->setResponse(new JsonResponse([
                'message' => $event->getThrowable()->getPrevious()->getMessage(),
                'errors' => $event->getThrowable()->getPrevious()->getErrors(),
            ], Response::HTTP_CONFLICT));

            return;
        }

        if ($event->getThrowable() instanceof UserEmailMustBeVerified) {
            $event->setResponse(new JsonResponse([
                'message' => $event->getThrowable()->getMessage(),
                'errors' => $event->getThrowable()->getErrors(),
            ], Response::HTTP_FORBIDDEN));

            return;
        }
    }
}
