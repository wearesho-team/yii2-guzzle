<?php

declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Log;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Horat1us\Yii\Validation;

class SyncRepository implements RepositoryInterface
{
    private Factory $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function save(RequestInterface $request, ?ResponseInterface $response, ?\Throwable $exception = null): void
    {
        $req = new Request($this->factory->fromRequest($request));
        Validation\Exception::saveOrThrow($req);
        if (!is_null($response)) {
            $res = new Response($this->factory->fromResponse($response));
            $res->setRequest($req);
            Validation\Exception::saveOrThrow($res);
        }
        if (!is_null($exception)) {
            $ex = new Exception($this->factory->fromException($exception));
            $ex->setRequest($req);
            Validation\Exception::saveOrThrow($ex);
        }
    }
}
