<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Voucher\Application\Exception\ProviderUserNotFound;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class VoucherApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        if ($throwable instanceof ProviderUserNotFound) {
            $event->setResponse(new JsonResponse([
                'message' => $throwable->getMessage(),
                'errors' => $throwable->getErrors(),
            ], Response::HTTP_FORBIDDEN));

            return;
        }
    }
}
