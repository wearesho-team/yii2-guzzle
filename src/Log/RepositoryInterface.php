<?php

declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Log;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

interface RepositoryInterface
{
    public function save(
        RequestInterface $request,
        ?ResponseInterface $response,
        ?\Throwable $exception = null
    ): void;
}
