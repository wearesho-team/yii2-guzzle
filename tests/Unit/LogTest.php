<?php

namespace Wearesho\Yii\Guzzle\Tests\Unit;

use GuzzleHttp;
use Psr\Http\Message;
use Wearesho\Yii\Guzzle;

/**
 * Class LogTest
 * @package Wearesho\Yii\Guzzle\Tests\Unit
 * @covers \Wearesho\Yii\Guzzle\Bootstrap::bootstrap()
 * @internal
 */
class LogTest extends Guzzle\Tests\TestCase
{
    protected const URI = 'www.example.com';
    protected const REQUEST_HEADERS = ['allow_redirects' => true];
    protected const RESPONSE_HEADERS = ['X-Guzzle-Redirect-History' => ['http://first-redirect']];
    protected const BODY = 'body';
    protected const STATUS_200 = 200;
    protected const STATUS_400 = 400;
    protected const METHOD_POST = 'POST';
    protected const EXCEPTION_MESSAGE = 'message';
    protected const EXCLUDE_DOMAIN = 'www.exclude_example.com';

    protected array $historyContainer = [];

    protected GuzzleHttp\Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        /**
         * @noinspection PhpUnhandledExceptionInspection
         * @var GuzzleHttp\Client $client
         */
        $this->client = $this->container->get(GuzzleHttp\ClientInterface::class);
        $this->client
            ->getConfig('handler')
            ->push(GuzzleHttp\Middleware::history($this->historyContainer));
    }

    public function testResponseWithStatus200(): void
    {
        $this->setMocks($this->mockResponse());

        $request = $this->mockRequest();
        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $this->client->send($request);
        $logResponse = Guzzle\Log\Response::find()->andWhere(['=', 'status', static::STATUS_200])->one();

        $this->assertEquals($response->getHeaders(), $logResponse->headers);
        $this->assertEquals((string)$request->getUri(), $logResponse->request->uri);
        $this->assertEquals($response->getBody(), $logResponse->body);
        $this->assertEquals($response->getStatusCode(), $logResponse->status);
    }

    public function testNotUtf8Body(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $body = GuzzleHttp\Psr7\stream_for(random_bytes(65536));

        $this->setMocks(new GuzzleHttp\Psr7\Response(
            static::STATUS_200,
            [],
            $body
        ));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->client->send(new GuzzleHttp\Psr7\Request(
            static::METHOD_POST,
            static::URI,
            [],
            GuzzleHttp\Psr7\stream_for(random_bytes(65536))
        ));

        $logResponse = Guzzle\Log\Response::find()->andWhere(['=', 'status', static::STATUS_200])->one();
        $this->assertEquals(Guzzle\Log\Response::NOT_UTF_8_BODY, $logResponse->body);
        $this->assertEquals(Guzzle\Log\Request::NOT_UTF_8_BODY, $logResponse->request->body);
    }

    public function testResponseWithStatus400(): void
    {
        $this->setMocks(
            $this->mockResponse(),
            $this->mockResponse(static::STATUS_400)
        );

        $request = $this->mockRequest();
        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $this->client->send($request);
        $logResponse = Guzzle\Log\Response::find()->andWhere(['=', 'status', static::STATUS_200])->one();

        $this->assertEquals($response->getHeaders(), $logResponse->headers);
        $this->assertEquals((string)$request->getUri(), $logResponse->request->uri);
        $this->assertEquals($response->getBody(), $logResponse->body);
        $this->assertEquals($response->getStatusCode(), $logResponse->status);

        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->client->send($request);
        } catch (\Exception $exception) {
            $logResponse = Guzzle\Log\Response::find()->andWhere(['=', 'status', static::STATUS_400])->one();

            $this->assertEquals(
                static::RESPONSE_HEADERS,
                $logResponse->headers
            );
            $this->assertEquals((string)$request->getUri(), $logResponse->request->uri);
            $this->assertEquals(static::BODY, $logResponse->body);
            $this->assertEquals(static::STATUS_400, $logResponse->status);
        }
    }

    public function testBadResponseException(): void
    {
        $request = $this->mockRequest();
        $this->setMocks(
            $this->mockResponse(),
            $this->mockBadResponseException($request)
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $this->client->send($request);
        $logResponse = Guzzle\Log\Response::find()->andWhere(['=', 'status', static::STATUS_200])->one();

        $this->assertEquals($response->getHeaders(), $logResponse->headers);
        $this->assertEquals((string)$request->getUri(), $logResponse->request->uri);
        $this->assertEquals($response->getBody(), $logResponse->body);
        $this->assertEquals($response->getStatusCode(), $logResponse->status);

        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->client->send($request);
        } catch (\Exception $ex) {
            $logException = Guzzle\Log\Exception::find()->andWhere(['=', 'type', get_class($ex)])->one();
            $this->assertSame($logException->type, get_class($ex));
        }
    }

    public function testBadResponseExceptionWithResponseTrace(): void
    {
        $request = $this->mockRequest();
        $response = $this->mockResponse(static::STATUS_400);
        $this->setMocks(
            $this->mockBadResponseException(
                $request,
                static::EXCEPTION_MESSAGE,
                $response
            )
        );

        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->client->send($request);
        } catch (\Exception $ex) {
            $logException = Guzzle\Log\Exception::find()->andWhere(['=', 'type', get_class($ex)])->one();
            $logResponse = Guzzle\Log\Response::find()->andWhere(['=', 'status', static::STATUS_400])->one();
            $this->assertSame($logException->type, get_class($ex));
            $this->assertEquals($response->getHeaders(), $logResponse->headers);
            $this->assertEquals((string)$request->getUri(), $logResponse->request->uri);
            $this->assertEquals($response->getBody(), $logResponse->body);
            $this->assertEquals($response->getStatusCode(), $logResponse->status);
        }
    }

    public function testExclude(): void
    {
        $request = $this->mockRequest();
        $excludeDomainRegexRequest = $this->mockRequest('GET', 'php.net');
        $this->setMocks(
            $this->mockResponse(201, [['header_1' => 'test']], 'body'),
            $this->mockResponse(404, [['header_2' => true]], json_encode(['json' => []])),
            $this->mockBadResponseException($excludeDomainRegexRequest),
            $this->mockBadResponseException($excludeDomainRegexRequest)
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $response = $this->client->send($request);
        $logResponse = Guzzle\Log\Response::find()->andWhere(['=', 'status', 201])->one();

        $this->assertEquals($response->getHeaders(), $logResponse->headers);
        $this->assertEquals((string)$request->getUri(), $logResponse->request->uri);
        $this->assertEquals($response->getBody(), $logResponse->body);
        $this->assertEquals($response->getStatusCode(), $logResponse->status);

        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->client->send($request);
        } catch (\Exception $ex) {
            $logException = Guzzle\Log\Response::find()->andWhere(['=', 'status', 404])->one();
            $this->assertSame($logException->body, json_encode(['json' => []]));
        }

        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->client->send($excludeDomainRegexRequest);
        } catch (\Exception $ex) {
            $logException = Guzzle\Log\Exception::find()->andWhere(['=', 'type', get_class($ex)])->one();
            $this->assertNull($logException);
        }

        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->client->send($excludeDomainRegexRequest);
        } catch (\Exception $ex) {
            $logException = Guzzle\Log\Exception::find()->andWhere(['=', 'type', get_class($ex)])->one();
            $this->assertNull($logException);
        }

        $this->assertCount(2, Guzzle\Log\Request::find()->all());
        $this->assertCount(2, Guzzle\Log\Response::find()->all());
        $this->assertCount(0, Guzzle\Log\Exception::find()->all());
    }

    protected function setMocks(object ...$mocks): void
    {
        $this->client->getConfig('handler')->setHandler(
            new GuzzleHttp\Handler\MockHandler($mocks)
        );
    }

    protected function mockResponse(
        int $status = self::STATUS_200,
        array $headers = self::RESPONSE_HEADERS,
        string $body = self::BODY
    ): GuzzleHttp\Psr7\Response {
        return new GuzzleHttp\Psr7\Response($status, $headers, $body);
    }

    protected function mockRequest(
        string $method = self::METHOD_POST,
        string $uri = self::URI,
        array $headers = self::REQUEST_HEADERS,
        string $body = self::BODY
    ): GuzzleHttp\Psr7\Request {
        return new GuzzleHttp\Psr7\Request($method, $uri, $headers, $body);
    }

    protected function mockBadResponseException(
        Message\RequestInterface $request,
        string $message = self::EXCEPTION_MESSAGE,
        Message\ResponseInterface $response = null,
        \Exception $previous = null
    ) {
        return new GuzzleHttp\Exception\BadResponseException($message, $request, $response, $previous);
    }
}
