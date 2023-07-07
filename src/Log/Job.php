<?php

declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Log;

use Horat1us\Yii\Validation;
use yii\queue;

class Job implements queue\JobInterface
{
    private array $request;
    private ?array $response;
    private ?array $exception;

    public function __construct(array $request, ?array $response, ?array $exception)
    {
        $this->request = $request;
        $this->response = $response;
        $this->exception = $exception;
    }

    public function execute($queue): void
    {
        $request = new Request($this->request);
        Validation\Exception::saveOrThrow($request);
        if (!is_null($this->response)) {
            $response = new Response($this->response);
            $response->setRequest($request);
            Validation\Exception::saveOrThrow($response);
        }
        if (!is_null($this->exception)) {
            $exception = new Exception($this->exception);
            $exception->setRequest($request);
            Validation\Exception::saveOrThrow($exception);
        }
    }
}
