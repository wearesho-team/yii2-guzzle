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
class RequestTest extends Guzzle\Tests\TestCase
{
    protected const METHOD = 'POST';
    protected const URI = 'uri';
    protected const HEADERS = [['header_1', 'header_2']];
    protected const BODY = 'body';

    /** @var Guzzle\Log\Request */
    protected $fakeRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeRequest = new Guzzle\Log\Request();
    }

    public function testValidateMethod(): void
    {
        $this->assertFalse($this->fakeRequest->validate('method'));

        $this->fakeRequest->method = static::METHOD;

        $this->assertTrue($this->fakeRequest->validate('method'));
    }

    public function testValidateUri(): void
    {
        $this->assertFalse($this->fakeRequest->validate('uri'));

        $this->fakeRequest->uri = static::URI;

        $this->assertTrue($this->fakeRequest->validate('uri'));
    }

    public function testValidateHeaders(): void
    {
        $this->assertFalse($this->fakeRequest->validate('headers'));

        $this->fakeRequest->headers = static::HEADERS;

        $this->assertTrue($this->fakeRequest->validate('headers'));
    }

    public function testValidateBody(): void
    {
        $this->fakeRequest->body = mt_rand();
        $this->assertFalse($this->fakeRequest->validate('body'));

        $this->fakeRequest->body = static::BODY;

        $this->assertTrue($this->fakeRequest->validate('body'));
    }

    public function testFullValidate(): void
    {
        $this->fakeRequest = Guzzle\Log\Request::create(
            new Request(static::METHOD, static::URI, static::HEADERS, static::BODY)
        );
        $this->assertTrue($this->fakeRequest->save());
    }
}
