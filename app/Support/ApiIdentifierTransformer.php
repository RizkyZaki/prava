<?php

namespace App\Support;

use Hashids\Hashids;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ApiIdentifierTransformer
{
    private static ?Hashids $hashids = null;

    private static function hashids(): Hashids
    {
        if (self::$hashids === null) {
            self::$hashids = new Hashids(
                config('app.key'),
                8,
                'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
            );
        }

        return self::$hashids;
    }
    public static function handleRequest(Request $request): void
    {
        $request->replace(self::transformArray($request->all(), false));

        $route = $request->route();

        if (! $route) {
            return;
        }

        foreach ($route->parameters() as $key => $value) {
            if (! self::isIdentifierKey((string) $key)) {
                continue;
            }

            $route->setParameter($key, self::decodeRouteIdentifier($value));
        }
    }

    public static function handleResponse(Response $response): Response
    {
        if (! self::isJsonResponse($response)) {
            return $response;
        }

        $content = $response->getContent();

        if ($content === false || $content === '' || $content === null) {
            return $response;
        }

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $response;
        }

        try {
            $response->setContent(json_encode(self::transformArray($decoded, true), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } catch (JsonException) {
            return $response;
        }

        return $response;
    }

    private static function isJsonResponse(Response $response): bool
    {
        return $response instanceof JsonResponse || str_contains((string) $response->headers->get('Content-Type'), 'json');
    }

    private static function transformArray(mixed $value, bool $encode): mixed
    {
        if ($value instanceof Arrayable) {
            $value = $value->toArray();
        }

        if (! is_array($value)) {
            return $encode && is_string($value) ? self::transformIdentifierInString($value) : $value;
        }

        $result = [];

        foreach ($value as $key => $item) {
            $result[$key] = self::transformArrayEntry($key, $item, $encode);
        }

        return $result;
    }

    private static function transformArrayEntry(string|int $key, mixed $value, bool $encode): mixed
    {
        if (is_string($key) && self::isIdentifierKey($key)) {
            return self::transformIdentifierValue($key, $value, $encode);
        }

        if (is_array($value) || $value instanceof Arrayable) {
            return self::transformArray($value, $encode);
        }

        if (is_string($value) && $encode) {
            return self::transformIdentifierInString($value);
        }

        return $value;
    }

    private static function transformIdentifierValue(string $key, mixed $value, bool $encode): mixed
    {
        if (is_array($value)) {
            if (str_ends_with($key, '_ids')) {
                return array_map(fn ($item) => self::transformScalarIdentifier($item, $encode), $value);
            }

            return self::transformArray($value, $encode);
        }

        if ($value instanceof Arrayable) {
            return self::transformArray($value->toArray(), $encode);
        }

        return self::transformScalarIdentifier($value, $encode);
    }

    private static function transformScalarIdentifier(mixed $value, bool $encode): mixed
    {
        if ($encode) {
            if (is_int($value) || is_float($value) || (is_string($value) && ctype_digit($value))) {
                return self::encodeIdentifier((string) $value);
            }

            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        return self::decodeIdentifier($value);
    }

    private static function encodeIdentifier(string $value): string
    {
        if (!ctype_digit($value)) {
            return $value;
        }

        return self::hashids()->encode((int) $value);
    }

    private static function decodeIdentifier(string $value): mixed
    {
        if ($value === '' || ctype_digit($value)) {
            return $value === '' ? $value : (int) $value;
        }

        $decoded = self::hashids()->decode($value);

        return empty($decoded) ? $value : $decoded[0];
    }

    private static function decodeRouteIdentifier(mixed $value): int|string
    {
        $decoded = self::decodeIdentifier((string) $value);

        if (is_int($decoded)) {
            return $decoded;
        }

        if (is_string($decoded) && ctype_digit($decoded)) {
            return (int) $decoded;
        }

        throw new NotFoundHttpException('Data not found');
    }

    private static function transformIdentifierInString(string $value): string
    {
        return (string) preg_replace_callback(
            '/([?&])([A-Za-z0-9_]*(?:id|_id|_ids))=([^&#]*)/i',
            static function (array $matches): string {
                $rawValue = rawurldecode($matches[3]);

                if ($rawValue === '' || ! ctype_digit($rawValue)) {
                    return $matches[0];
                }

                return $matches[1] . $matches[2] . '=' . rawurlencode(self::encodeIdentifier($rawValue));
            },
            $value
        );
    }

    private static function isIdentifierKey(string $key): bool
    {
        return $key === 'id' || str_ends_with($key, '_id') || str_ends_with($key, '_ids');
    }
}
