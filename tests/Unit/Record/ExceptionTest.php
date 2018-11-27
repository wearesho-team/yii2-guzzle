<?php

namespace Wearesho\Yii\Guzzle\Tests\Unit\Record;

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

    public function testValidateType(): void
    {
        $this->assertFalse($this->exception->validate('type'));

        $this->exception->type = static::TYPE;

        $this->assertTrue($this->exception->validate('type'));
    }

    public function testValidateTrace(): void
    {
        $this->assertFalse($this->exception->validate('trace'));

        $this->exception->trace = static::TRACE;

        $this->assertTrue($this->exception->validate('trace'));
    }

    public function testValidateRelatedRequest(): void
    {
        $this->assertFalse($this->exception->validate('http_request_id'));

        $httpRequest = Guzzle\Log\Request::create(new Request('GET', 'uri', static::HEADERS));
        $this->assertTrue($httpRequest->save());
        $this->exception->http_request_id = $httpRequest->id;

        $this->assertTrue($this->exception->validate('http_request_id'));
    }

    public function testFullValidate(): void
    {
        $this->assertFalse($this->exception->validate());

        $this->exception = new Guzzle\Log\Exception([
            'request' => Guzzle\Log\Request::create(new Request('GET', 'uri', static::HEADERS)),
            'trace' => static::TRACE,
            'type' => static::TYPE,
        ]);

        $this->assertTrue($this->exception->validate());
    }
}
