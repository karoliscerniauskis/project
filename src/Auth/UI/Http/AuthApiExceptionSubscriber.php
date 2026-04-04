<?php

declare(strict_types=1);

namespace App\Auth\UI\Http;

use App\Auth\Application\Exception\UserEmailMustBeUnique;
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
        $exception = $event->getThrowable()->getPrevious();

        if ($exception instanceof UserEmailMustBeUnique) {
            $event->setResponse(new JsonResponse([
                'message' => $exception->getMessage(),
                'errors' => $exception->getErrors(),
            ], Response::HTTP_CONFLICT));

            return;
        }
    }
}
