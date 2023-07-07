<?php

declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Log;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use yii\queue;
use yii\base;
use yii\di;

class QueueRepository extends base\BaseObject implements RepositoryInterface
{
    /** @var string|array|queue\Queue */
    public $queue = 'queue';

    /** @var string|array|Factory */
    public $factory = Factory::class;

    public function init(): void
    {
        parent::init();
        $this->queue = di\Instance::ensure($this->queue, queue\Queue::class);
        $this->factory = di\Instance::ensure($this->factory, Factory::class);
    }

    public function save(RequestInterface $request, ?ResponseInterface $response, ?\Throwable $exception = null): void
    {
        $job = new Job(
            $this->factory->fromRequest($request),
            is_null($response) ? null : $this->factory->fromResponse($response),
            is_null($exception) ? null : $this->factory->fromException($exception)
        );
        $this->queue->push($job);
    }
}
