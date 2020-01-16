<?php

namespace Wearesho\Yii\Guzzle;

use Horat1us\Yii\Traits\BootstrapMigrations;
use GuzzleHttp;
use Psr\Http\Message;
use Wearesho\Yii\Guzzle\Log;
use yii\base;
use yii\console;

/**
 * Class Bootstrap
 * @package Wearesho\Yii\Guzzle
 */
class Bootstrap extends base\BaseObject implements base\BootstrapInterface
{
    use BootstrapMigrations;

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
    public $exclude = [];

    /**
     * Guzzle client configuration settings.
     * @see \GuzzleHttp\Client::__construct()
     *
     * Note: you can't use handler key
     *
     * @var array
     */
    public $config = [];

    /**
     * @param base\Application $app
     *
     * @throws base\InvalidConfigException
     */
    public function bootstrap($app)
    {
        foreach ((array)$this->exclude as $regular) {
            if (@preg_match($regular, '') === false) {
                throw new base\InvalidConfigException("Given regular expression invalid: " . $regular);
            }
        }
        \Yii::setAlias('Wearesho/Yii/Guzzle', '@vendor/wearesho-team/yii2-guzzle/src');

        if ($app instanceof console\Application) {
            $this->appendMigrations($app, 'Wearesho\\Yii\\Guzzle\\Migrations');
        }

        $handler = function (callable $handler) {
            return function (Message\RequestInterface $request, array $options) use ($handler) {
                $handler = call_user_func($handler, $request, $options);
                foreach ((array)$this->exclude as $domain) {
                    if (preg_match((string)$domain, (string)$request->getUri())) {
                        return $handler;
                    }
                }

                $logRequest = Log\Request::create($request);
                return $handler->then(
                    function (Message\ResponseInterface $response) use ($logRequest) {
                        Log\Response::create($response, $logRequest);
                        return $response;
                    },
                    function ($reason) use ($logRequest) {
                        $reason instanceof \Throwable && Log\Exception::create($reason, $logRequest);
                        $response = $reason instanceof GuzzleHttp\Exception\RequestException
                            ? $reason->getResponse()
                            : null;
                        if ($response) {
                            Log\Response::create($response, $logRequest);
                        }

                        return GuzzleHttp\Promise\rejection_for($reason);
                    }
                );
            };
        };
        $handlerStack = GuzzleHttp\HandlerStack::create();
        $handlerStack->push($handler);

        \Yii::$container->set(
            GuzzleHttp\ClientInterface::class,
            function (
                /** @noinspection PhpUnusedParameterInspection */ $container,
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
