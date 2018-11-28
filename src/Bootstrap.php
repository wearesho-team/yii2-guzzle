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
     * Urls that should not be logged
     *
     * @var array Urls
     *
     * @example Url: https://john.doe@www.example.com:123/forum/questions/?tag=networking&order=newest#top
     *          Url: 'https://www.example.com/'
     */
    public $excludedDomains = [];

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
                $uri = $request->getUri();
                $doLog = !in_array($uri->getScheme() . $uri->getHost(), $this->excludedDomains);

                $logRequest = $doLog ? Log\Request::create($request) : null;
                return $handler($request, $options)->then(
                    function (Message\ResponseInterface $response) use ($logRequest) {
                        !$logRequest ?: Log\Response::create($response, $logRequest);
                        return $response;
                    },
                    function ($reason) use ($logRequest) {
                        if ($logRequest) {
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
