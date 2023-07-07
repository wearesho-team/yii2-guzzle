<?php

namespace Wearesho\Yii\Guzzle\Tests\Unit\Record;

use GuzzleHttp\Psr7\Request;
use Wearesho\Yii\Guzzle;

/**
 * Class RequestTest
 * @package Wearesho\Yii\Guzzle\Tests\Unit\Record
 * @coversDefaultClass \Wearesho\Yii\Guzzle\Log\Request
 * @internal
 */
class RequestTest extends TestCase
{
    protected const METHOD = 'POST';
    protected const URI = 'uri';
    protected const HEADERS = [['header_1', 'header_2']];
    protected const BODY = 'body';

    /** @var Guzzle\Log\Request */
    protected $record = Guzzle\Log\Request::class;

    public function testFailedValidate(): void
    {
        $this->assertFalse($this->record->validate());
    }

    /**
     * @depends testFailedValidate
     */
    public function testValidateMethod(): void
    {
        $this->record->method = static::METHOD;

        $this->assertTrue($this->record->validate('method'));
    }

    /**
     * @depends testFailedValidate
     */
    public function testValidateUri(): void
    {
        $this->record->uri = static::URI;

        $this->assertTrue($this->record->validate('uri'));
    }

    /**
     * @depends testFailedValidate
     */
    public function testValidateHeaders(): void
    {
        $this->record->headers = static::HEADERS;

        $this->assertTrue($this->record->validate('headers'));
    }

    /**
     * @depends testFailedValidate
     */
    public function testValidateBody(): void
    {
        $this->record->body = static::BODY;

        $this->assertTrue($this->record->validate('body'));
    }

    public function testFullValidate(): void
    {
        $factory = new Guzzle\Log\Factory();
        $this->record = new Guzzle\Log\Request(
            $factory->fromRequest(
                new Request(static::METHOD, static::URI, static::HEADERS, static::BODY)
            )
        );

        $this->assertTrue($this->record->save());
    }
}
