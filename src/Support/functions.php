<?php

declare(strict_types=1);

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;

if (!function_exists('response')) {
    /**
     * @param mixed $response
     * @param int   $status
     * @param array $headers
     *
     * @return ResponseInterface
     */
    function response(mixed $response, int $status = 200, array $headers = []): ResponseInterface
    {
        $type = gettype($response);

        return match ($type) {
            'object'    => new JsonResponse([
                'data' => (array) $response
            ], $status, $headers),
            'string'    => new HtmlResponse($response, $status, $headers),
            default     => new JsonResponse([
                'data' => $response
            ], $status, $headers)
        };
    }
}

if (!function_exists('json')) {
    /**
     * @param mixed $data
     * @param int   $status
     * @param array $headers
     * @param int   $encodingOptions
     *
     * @return ResponseInterface|JsonResponse
     */
    function json(
        mixed $data,
        int $status = 200,
        array $headers = ['Content-Type' => ['application/hal+json']],
        int $encodingOptions = JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    ): JsonResponse {
        return new JsonResponse([
            'data' => $data
        ], $status, $headers, $encodingOptions);
    }
}

if (!function_exists('redirect')) {
    /**
     * @param UriInterface|string $uri
     * @param int                 $status
     * @param array               $headers
     *
     * @return RedirectResponse
     */
    function redirect(mixed $uri = '/', int $status = 302, array $headers = []): RedirectResponse
    {
        return new RedirectResponse($uri, $status, $headers);
    }
}
