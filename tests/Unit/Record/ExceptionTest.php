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
class ExceptionTest extends TestCase
{
    protected const TYPE = 'test_type';
    protected const HEADERS = ['test_header'];
    protected const TRACE = ['test_trace'];

    /** @var \Wearesho\Yii\Guzzle\Log\Exception */
    protected $record = Guzzle\Log\Exception::class;

    public function testFailedValidate(): void
    {
        $this->assertFalse($this->record->validate());
    }

    /**
     * @depends testFailedValidate
     */
    public function testValidateType(): void
    {
        $this->record->type = static::TYPE;

        $this->assertTrue($this->record->validate('type'));
    }

    /**
     * @depends testFailedValidate
     */
    public function testValidateTrace(): void
    {
        $this->record->trace = static::TRACE;

        $this->assertTrue($this->record->validate('trace'));
    }

    /**
     * @depends testFailedValidate
     */
    public function testValidateRelatedRequest(): void
    {
        $httpRequest = Guzzle\Log\Request::create(new Request('GET', 'uri', static::HEADERS));
        $this->assertTrue($httpRequest->save());
        $this->record->http_request_id = $httpRequest->id;

        $this->assertTrue($this->record->validate('http_request_id'));
    }

    /**
     * @depends testFailedValidate
     */
    public function testFullValidate(): void
    {
        $this->record = new Guzzle\Log\Exception([
            'request' => Guzzle\Log\Request::create(new Request('GET', 'uri', static::HEADERS)),
            'trace' => static::TRACE,
            'type' => static::TYPE,
        ]);
        $this->record->save();

        $this->assertNotEmpty($this->record->created_at);
        $this->assertTrue($this->record->validate());
    }

    /**
     * @depends testFailedValidate
     */
    public function testCreateByGuzzleException(): void
    {
        $httpRequest = new Request('GET', 'uri', static::HEADERS);
        $this->record = Guzzle\Log\Exception::create(
            new RequestException('Message', $httpRequest),
            Guzzle\Log\Request::create($httpRequest)
        );

        $this->assertTrue($this->record->save());
    }
}
