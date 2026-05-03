<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiWebTestCase extends WebTestCase
{
    /**
     * @param array<string, mixed> $payload
     */
    protected static function json(array $payload): string
    {
        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        return $json;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function getJsonResponse(string|false $content): array
    {
        self::assertIsString($content);

        /** @var array<string, mixed> $response */
        $response = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $response;
    }
}
