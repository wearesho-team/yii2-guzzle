<?php

declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Tests\Unit\Record;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Horat1us\Yii\Validation;
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
        $httpRequest = $this->createRequest();
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
            'request' => $this->createRequest(),
            'trace' => static::TRACE,
            'type' => static::TYPE,
        ]);
        $this->assertTrue($this->record->save());

        $this->assertNotEmpty($this->record->created_at);
        $this->assertTrue($this->record->validate());
    }

    /**
     * @depends testFailedValidate
     */
    public function testCreateByGuzzleException(): void
    {
        $this->record = Guzzle\Log\Exception::create(
            new RequestException('Message', new Request('GET', 'uri', static::HEADERS)),
            $this->createRequest()
        );

        $this->assertTrue($this->record->save());
    }

    public function testTraceKeys(): void
    {
        $factory = new Guzzle\Log\Factory();

        $log = new Guzzle\Log\Exception($factory->fromException(new \Exception()));
        $log->setRequest($this->createRequest());

        $this->assertGreaterThan(0, count($log->trace));
        foreach ($log->trace as $trace) {
            $this->assertArrayHasKey('file', $trace);
            $this->assertArrayHasKey('line', $trace);
            $this->assertArrayHasKey('function', $trace);
            $this->assertArrayNotHasKey('args', $trace);
            $this->assertArrayNotHasKey('type', $trace);
        }
    }

    private function createRequest(): Guzzle\Log\Request
    {
        $factory = new Guzzle\Log\Factory();
        $request = new Guzzle\Log\Request(
            $factory->fromRequest(
                new Request('GET', 'uri', static::HEADERS)
            )
        );
        Validation\Exception::saveOrThrow($request);
        return $request;
    }
}
