<?php

namespace Wearesho\Yii\Guzzle;

use Wearesho\Yii\Guzzle\Log;
use yii\console;
use GuzzleHttp;
use yii\base;
use yii\di;

/**
 * Class Bootstrap
 * @package Wearesho\Yii\Guzzle
 */
class Bootstrap extends base\BaseObject implements base\BootstrapInterface
{
    /**
     * URLs that should not be logged.
     * They will be compared as regular expression.
     *
     * You can put an object here that implement __toString() method
     *
     * @var array
     *
     * @example '/^(https|http):\/\/maps.googleapis.com\/.*$/' Will exclude urls to google api from logging
     */
    public array $exclude = [];

    /**
     * Guzzle client configuration settings.
     * @see \GuzzleHttp\Client::__construct()
     *
     * Note: you can't use handler key
     *
     * @var array
     */
    public array $config = [];

    /**
     * Repository configuration
     *
     * @see Log\RepositoryInterface
     * @see Log\SyncRepository
     * @see Log\QueueRepository
     *
     * @var array
     */
    public array $repository = [
        'class' => Log\SyncRepository::class,
    ];

    /**
     * @param base\Application $app
     *
     * @throws base\InvalidConfigException
     */
    public function bootstrap($app)
    {
        if ($app instanceof console\Application) {
            (new Migrations\Bootstrap())->bootstrap($app);
        }

        foreach ((array)$this->exclude as $regular) {
            if (@preg_match($regular, '') === false) {
                throw new base\InvalidConfigException("Given regular expression invalid: " . $regular);
            }
        }

        /** @var Log\RepositoryInterface $repository */
        $repository = di\Instance::ensure($this->repository, Log\RepositoryInterface::class);
        \Yii::$container->setSingleton(Log\RepositoryInterface::class, $repository);

        $middleware = new Log\Middleware($repository, $this->exclude);
        \Yii::$container->setSingleton(Log\Middleware::class, fn() => $middleware);

        $handlerStack = GuzzleHttp\HandlerStack::create();
        $handlerStack->push($middleware);
        \Yii::$container->setSingleton(GuzzleHttp\HandlerStack::class, fn() => $handlerStack);

        \Yii::$container->set(
            GuzzleHttp\ClientInterface::class,
            function (
                $container,
                $params,
                $config
            ) use ($handlerStack) {
                return new GuzzleHttp\Client(...$params + [
                        0 => $config + [
                                'handler' => $handlerStack,
                            ] + $this->config,
                    ]);
            }
        );
    }
}
