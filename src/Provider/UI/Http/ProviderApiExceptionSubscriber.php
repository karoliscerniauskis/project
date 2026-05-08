<?php

declare(strict_types=1);

namespace App\Provider\UI\Http;

use App\Provider\Application\Exception\ProviderAccessDenied;
use App\Provider\Application\Exception\ProviderAdminRoleRequired;
use App\Provider\Application\Exception\ProviderNameAlreadyExists;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ProviderApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        if ($event->getThrowable()->getPrevious() instanceof ProviderNameAlreadyExists) {
            $event->setResponse(new JsonResponse([
                'message' => $event->getThrowable()->getPrevious()->getMessage(),
                'errors' => $event->getThrowable()->getPrevious()->getErrors(),
            ], Response::HTTP_CONFLICT));

            return;
        }

        if ($event->getThrowable()->getPrevious() instanceof ProviderAccessDenied) {
            $event->setResponse(new JsonResponse([
                'message' => $event->getThrowable()->getPrevious()->getMessage(),
                'errors' => $event->getThrowable()->getPrevious()->getErrors(),
            ], Response::HTTP_FORBIDDEN));

            return;
        }

        if ($event->getThrowable()->getPrevious() instanceof ProviderAdminRoleRequired) {
            $event->setResponse(new JsonResponse([
                'message' => $event->getThrowable()->getPrevious()->getMessage(),
                'errors' => $event->getThrowable()->getPrevious()->getErrors(),
            ], Response::HTTP_FORBIDDEN));
        }
    }
}
