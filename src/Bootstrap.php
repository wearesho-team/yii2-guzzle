<?php

namespace Wearesho\Yii\Guzzle;

use Psr\Http\Message;
use yii\base;
use yii\console;
use GuzzleHttp;
use Horat1us\Yii\Traits\BootstrapMigrations;
use Wearesho\Yii\Guzzle\Log;

/**
 * Class Bootstrap
 * @package Wearesho\Yii\Guzzle
 */
class Bootstrap extends base\BaseObject implements base\BootstrapInterface
{
    use BootstrapMigrations;

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
                $logRequest = Log\Request::create($request);
                return $handler($request, $options)->then(
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
