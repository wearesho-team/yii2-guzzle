<?php

namespace Wearesho\Yii\Guzzle\Tests\Unit;

use GuzzleHttp;
use Wearesho\Yii\Guzzle;

/**
 * Class LogTest
 * @package Wearesho\Yii\Guzzle\Tests\Unit
 */
class LogTest extends Guzzle\Tests\TestCase
{
    protected const URI = 'www.example.com';
    protected const REQUEST_HEADERS = ['allow_redirects' => true];
    protected const RESPONSE_HEADERS = ['X-Guzzle-Redirect-History' => 'http://first-redirect'];
    protected const BODY = 'body';
    protected const STATUS_200 = 200;

    /** @var array */
    protected $historyContainer = [];

    /** @var GuzzleHttp\Client */
    protected $client;

    /** @var GuzzleHttp\HandlerStack */
    protected $handler;

    protected function setUp()
    {
        parent::setUp();

        /**
         * @noinspection PhpUnhandledExceptionInspection
         * @var GuzzleHttp\Client $client
         */
        $this->client = $this->container->get(GuzzleHttp\ClientInterface::class);
        /** @var GuzzleHttp\HandlerStack $handler */
        $this->handler = $this->client->getConfig('handler');
        $this->handler->push(GuzzleHttp\Middleware::history($this->historyContainer));
    }

    public function testLogResponse(): void
    {
        $mock = new GuzzleHttp\Handler\MockHandler([
            $this->mockResponse(),
        ]);
        $this->client->getConfig('handler')->setHandler($mock);
        $request = new GuzzleHttp\Psr7\Request('POST', static::URI, static::REQUEST_HEADERS);
        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $this->client->send($request);
        $logResponse = Guzzle\Log\Response::find()->andWhere(['=', 'status', static::STATUS_200])->one();

        $this->assertEquals($response->getHeaders(), $logResponse->headers);
        $this->assertEquals((string)$request->getUri(), $logResponse->request->uri);
        $this->assertEquals($response->getBody(), $logResponse->body);
        $this->assertEquals($response->getStatusCode(), $logResponse->status);
    }

    protected function mockResponse(): GuzzleHttp\Psr7\Response
    {
        return new GuzzleHttp\Psr7\Response(static::STATUS_200, static::RESPONSE_HEADERS, static::BODY);
    }
}
