<?php

declare(strict_types=1);

namespace App\Voucher\UI\Http;

use App\Voucher\Application\Exception\ProviderUserNotFound;
use App\Voucher\Application\Exception\VoucherAccessDenied;
use App\Voucher\Application\Exception\VoucherAlreadyClaimed;
use App\Voucher\Application\Exception\VoucherIssuedToEmailMismatch;
use App\Voucher\Application\Exception\VoucherNotActive;
use App\Voucher\Application\Exception\VoucherNotFound;
use App\Voucher\Application\Exception\VoucherProviderMismatch;
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
        if ($event->getThrowable()->getPrevious() instanceof ProviderUserNotFound) {
            $event->setResponse(new JsonResponse([
                'message' => $event->getThrowable()->getPrevious()->getMessage(),
                'errors' => $event->getThrowable()->getPrevious()->getErrors(),
            ], Response::HTTP_FORBIDDEN));

            return;
        }

        if ($event->getThrowable()->getPrevious() instanceof VoucherNotFound) {
            $event->setResponse(new JsonResponse([
                'message' => $event->getThrowable()->getPrevious()->getMessage(),
                'errors' => $event->getThrowable()->getPrevious()->getErrors(),
            ], Response::HTTP_NOT_FOUND));

            return;
        }

        if ($event->getThrowable()->getPrevious() instanceof VoucherProviderMismatch) {
            $event->setResponse(new JsonResponse([
                'message' => $event->getThrowable()->getPrevious()->getMessage(),
                'errors' => $event->getThrowable()->getPrevious()->getErrors(),
            ], Response::HTTP_CONFLICT));

            return;
        }

        if ($event->getThrowable()->getPrevious() instanceof VoucherNotActive) {
            $event->setResponse(new JsonResponse([
                'message' => $event->getThrowable()->getPrevious()->getMessage(),
                'errors' => $event->getThrowable()->getPrevious()->getErrors(),
            ], Response::HTTP_CONFLICT));

            return;
        }

        if ($event->getThrowable()->getPrevious() instanceof VoucherAlreadyClaimed) {
            $event->setResponse(new JsonResponse([
                'message' => $event->getThrowable()->getPrevious()->getMessage(),
                'errors' => $event->getThrowable()->getPrevious()->getErrors(),
            ], Response::HTTP_CONFLICT));

            return;
        }

        if ($event->getThrowable()->getPrevious() instanceof VoucherIssuedToEmailMismatch) {
            $event->setResponse(new JsonResponse([
                'message' => $event->getThrowable()->getPrevious()->getMessage(),
                'errors' => $event->getThrowable()->getPrevious()->getErrors(),
            ], Response::HTTP_CONFLICT));

            return;
        }

        if ($event->getThrowable()->getPrevious() instanceof VoucherAccessDenied) {
            $event->setResponse(new JsonResponse([
                'message' => $event->getThrowable()->getPrevious()->getMessage(),
                'errors' => $event->getThrowable()->getPrevious()->getErrors(),
            ], Response::HTTP_FORBIDDEN));

            return;
        }
    }
}
