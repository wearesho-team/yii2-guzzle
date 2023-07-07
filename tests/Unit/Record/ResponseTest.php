<?php

namespace Wearesho\Yii\Guzzle\Tests\Unit\Record;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Wearesho\Yii\Guzzle;

/**
 * Class ResponseTest
 * @package Wearesho\Yii\Guzzle\Tests\Unit\Record
 */
class ResponseTest extends TestCase
{
    protected const HEADERS = [['header_1', 'header_2']];
    protected const STATUS = 200;

    /** @var Guzzle\Log\Response */
    protected $record = Guzzle\Log\Response::class;

    public function testValidate(): void
    {
        $this->assertFalse($this->record->validate());
    }

    /**
     * @depends testValidate
     */
    public function testValidateHeaders(): void
    {
        $this->record->headers = static::HEADERS;

        $this->assertTrue($this->record->validate('headers'));
    }

    /**
     * @depends testValidate
     */
    public function testValidateStatus(): void
    {
        $this->record->status = static::STATUS;

        $this->assertTrue($this->record->validate('status'));
    }

    /**
     * @depends testValidate
     */
    public function testValidateRequest(): void
    {
        $factory = new Guzzle\Log\Factory();
        $request = new Guzzle\Log\Request(
            $factory->fromRequest(
                new Request('POST', 'www.example.com', static::HEADERS)
            )
        );
        $request->save();
        $this->record->setRequest($request);

        $this->assertTrue($this->record->validate('http_request_id'));
    }

    /**
     * @depends testValidate
     */
    public function testFullValidate(): void
    {
        $factory = new Guzzle\Log\Factory();

        $request = new Guzzle\Log\Request(
            $factory->fromRequest(new Request('POST', 'www.example.com', static::HEADERS))
        );
        $request->save();

        $response = new Guzzle\Log\Response(
            $factory->fromResponse(new Response(static::STATUS, static::HEADERS))
        );
        $response->setRequest($request);

        $this->record = $response;

        $this->assertTrue($this->record->save());
        $this->assertEquals($this->record->request, $request);
    }
}
