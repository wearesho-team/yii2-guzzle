<?php

namespace Wearesho\Yii\Guzzle\Tests\Unit\Record;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Wearesho\Yii\Guzzle;

/**
 * Class ExceptionTest
 * @package Wearesho\Yii\Guzzle\Tests\Unit\Record
 * @coversDefaultClass \Wearesho\Yii\Guzzle\Log\Exception
 * @internal
 */
class ExceptionTest extends Guzzle\Tests\TestCase
{
    protected const TYPE = 'test_type';
    protected const HEADERS = ['test_header'];
    protected const TRACE = ['test_trace'];

    /** @var \Wearesho\Yii\Guzzle\Log\Exception */
    protected $exception;

    protected function setUp()
    {
        parent::setUp();

        $this->exception = new Guzzle\Log\Exception();
    }

    public function testFailedValidate(): void
    {
        $this->assertFalse($this->exception->validate());
    }

    /**
     * @depends testFailedValidate
     */
    public function testValidateType(): void
    {
        $this->exception->type = static::TYPE;

        $this->assertTrue($this->exception->validate('type'));
    }

    /**
     * @depends testFailedValidate
     */
    public function testValidateTrace(): void
    {
        $this->exception->trace = static::TRACE;

        $this->assertTrue($this->exception->validate('trace'));
    }

    /**
     * @depends testFailedValidate
     */
    public function testValidateRelatedRequest(): void
    {
        $httpRequest = Guzzle\Log\Request::create(new Request('GET', 'uri', static::HEADERS));
        $this->assertTrue($httpRequest->save());
        $this->exception->http_request_id = $httpRequest->id;

        $this->assertTrue($this->exception->validate('http_request_id'));
    }

    /**
     * @depends testFailedValidate
     */
    public function testFullValidate(): void
    {
        $this->exception = new Guzzle\Log\Exception([
            'request' => Guzzle\Log\Request::create(new Request('GET', 'uri', static::HEADERS)),
            'trace' => static::TRACE,
            'type' => static::TYPE,
        ]);
        $this->exception->save();

        $this->assertNotEmpty($this->exception->created_at);
        $this->assertTrue($this->exception->validate());
    }

    /**
     * @depends testFailedValidate
     */
    public function testCreateByGuzzleException(): void
    {
        $httpRequest = new Request('GET', 'uri', static::HEADERS);
        $this->exception = Guzzle\Log\Exception::create(
            new RequestException('Message', $httpRequest),
            Guzzle\Log\Request::create($httpRequest)
        );

        $this->assertTrue($this->exception->validate());
    }
}
