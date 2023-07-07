<?php

declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Log;

use GuzzleHttp;
use Psr\Http\Message;

class Middleware
{
    /** @var string[]|\Closure[] */
    private array $exclude;

    private RepositoryInterface $repository;

    public function __construct(RepositoryInterface $repository, array $exclude = [])
    {
        foreach ($exclude as $key => $rule) {
            if (is_callable($rule)) {
                continue;
            }
            if (is_string($rule) && (preg_match($rule, '') !== false)) {
                continue;
            }
            throw new \InvalidArgumentException(
                "Invalid exclude rule [$key]: must be \\Closure or RegExp string"
            );
        }
        $this->exclude = $exclude;
        $this->repository = $repository;
    }

    public function __invoke(callable $handler): \Closure
    {
        return function (Message\RequestInterface $request, array $options) use ($handler) {
            $handler = call_user_func($handler, $request, $options);
            foreach ($this->exclude as $rule) {
                if (is_callable($rule)) {
                    if (call_user_func($rule, $request)) {
                        return $handler;
                    }
                    continue;
                }
                if (preg_match((string)$rule, (string)$request->getUri())) {
                    return $handler;
                }
            }

            return $handler->then(
                function (Message\ResponseInterface $response) use ($request) {
                    $this->repository->save($request, $response);
                    return $response;
                },
                function ($reason) use ($request) {
                    $exception = $reason instanceof \Throwable ? $reason : null;
                    $response = $reason instanceof GuzzleHttp\Exception\RequestException
                        ? $reason->getResponse()
                        : null;
                    $this->repository->save($request, $response, $exception);

                    return GuzzleHttp\Promise\Create::rejectionFor($reason);
                }
            );
        };
    }
}
