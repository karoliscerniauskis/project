<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\HaveIBeenPwned;

use App\Auth\Application\EmailBreach\EmailBreachChecker;
use App\Auth\Application\EmailBreach\EmailBreachCheckResult;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class HaveIBeenPwnedEmailBreachChecker implements EmailBreachChecker
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
        private string $apiUrl,
    ) {
    }

    public function check(string $email): EmailBreachCheckResult
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                sprintf($this->apiUrl, rawurlencode($email)),
                [
                    'headers' => [
                        'hibp-api-key' => $this->apiKey,
                        'user-agent' => 'voucher-platform',
                    ],
                    'query' => [
                        'truncateResponse' => 'true',
                    ],
                    'timeout' => 10,
                ],
            );

            $statusCode = $response->getStatusCode();

            if ($statusCode === Response::HTTP_NOT_FOUND) {
                return new EmailBreachCheckResult(false, 0);
            }

            if ($statusCode !== Response::HTTP_OK) {
                return new EmailBreachCheckResult(false, 0);
            }

            /** @var array<int, array<string, mixed>> $breaches */
            $breaches = $response->toArray(false);

            return new EmailBreachCheckResult(count($breaches) > 0, count($breaches));
        } catch (ExceptionInterface) {
            return new EmailBreachCheckResult(false, 0);
        }
    }
}
