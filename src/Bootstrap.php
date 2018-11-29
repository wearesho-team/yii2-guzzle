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
     * @example '/^(https|http)://maps.googleapis.com/.*$/' Will exclude urls to google api from logging
     */
    public $excludedDomainsRegexes = [];

    /**
     * URLs that should not be logged.
     * They will be compared as plain string.
     *
     * @var array
     *
     * @example 'http://www.example.com/'
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
                $handler = $handler($request, $options);
                $uri = (string)$request->getUri();

                if (in_array($uri, $this->excludedDomains, true)) {
                    return $handler;
                }
                
                foreach ($this->excludedDomainsRegexes as $domain) {
                    if (preg_match((string)$domain, $uri)) {
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
