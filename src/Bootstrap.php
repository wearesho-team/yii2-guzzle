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
     * They will be compared with the current and strictly
     *
     * @var array
     *
     * @example
     * URL to exclude:   https://www.example.com/home
     * Will exclude:     https://www.example.com/home
     * Will NOT exclude: https://www.example.com/
     *                   https://www.example.com:123/
     *                   http://www.example.com:/home
     */
    public $excludedUrls = [];

    /**
     * @param base\Application $app
     */
    public function bootstrap($app)
    {
        \Yii::setAlias('Wearesho/Yii/Guzzle', '@vendor/wearesho-team/yii2-guzzle/src');

        if ($app instanceof console\Application) {
            $this->appendMigrations($app, 'Wearesho\\Yii\\Guzzle\\Migrations');
        }

        $handler = function (\Closure $handler) {
            return function (Message\RequestInterface $request, array $options) use ($handler) {
                $doLog = !in_array((string)$request->getUri(), $this->excludedUrls, true);

                $logRequest = $doLog ? Log\Request::create($request) : null;
                return $handler($request, $options)->then(
                    function (Message\ResponseInterface $response) use ($logRequest, $doLog) {
                        !$doLog ?: Log\Response::create($response, $logRequest);
                        return $response;
                    },
                    function ($reason) use ($logRequest, $doLog) {
                        if ($doLog) {
                            $reason instanceof \Throwable && Log\Exception::create($reason, $logRequest);
                            $response = $reason instanceof GuzzleHttp\Exception\RequestException
                                ? $reason->getResponse()
                                : null;
                            if ($response) {
                                Log\Response::create($response, $logRequest);
                            }
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
            function ($container, $params, $config) use ($handlerStack) {
                return new GuzzleHttp\Client(...$params + [
                        0 => $config + [
                                'handler' => $handlerStack,
                            ]
                    ]);
            }
        );
    }
}
