<?php

declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Log;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class Factory
{
    public const NOT_UTF_8_BODY = "(invalid UTF-8 bytes)";

    public function fromRequest(RequestInterface $request): array
    {
        $body = (string)$request->getBody();
        if (!mb_check_encoding($body, 'UTF-8')) {
            $body = static::NOT_UTF_8_BODY;
        }

        return [
            'method' => $request->getMethod(),
            'uri' => (string)$request->getUri(),
            'headers' => $request->getHeaders(),
            'body' => $body,
        ];
    }

    public function fromResponse(ResponseInterface $response): array
    {
        $body = (string)$response->getBody();
        if (!mb_check_encoding($body, "UTF-8")) {
            $body = static::NOT_UTF_8_BODY;
        }

        return [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => $body,
        ];
    }

    public function fromException(\Throwable $exception): array
    {
        $trace = array_map(
            fn(array $data): array => array_intersect_key($data, array_flip([
                'file',
                'line',
                'function',
                'class',
            ])),
            $exception->getTrace()
        );
        return [
            'type' => get_class($exception),
            'trace' => $trace,
        ];
    }
}
