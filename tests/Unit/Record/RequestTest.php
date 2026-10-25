<?php

namespace Wearesho\Yii\Guzzle\Tests\Unit\Record;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use GuzzleHttp\Psr7\Request;
use Wearesho\Yii\Guzzle;

/**
 * Class RequestTest
 * @package Wearesho\Yii\Guzzle\Tests\Unit\Record
 * @internal
 */
#[CoversClass(\Wearesho\Yii\Guzzle\Log\Request::class)]
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

    #[Depends('testFailedValidate')]
    public function testValidateMethod(): void
    {
        $this->record->method = static::METHOD;

        $this->assertTrue($this->record->validate('method'));
    }

    #[Depends('testFailedValidate')]
    public function testValidateUri(): void
    {
        $this->record->uri = static::URI;

        $this->assertTrue($this->record->validate('uri'));
    }

    #[Depends('testFailedValidate')]
    public function testValidateHeaders(): void
    {
        $this->record->headers = static::HEADERS;

        $this->assertTrue($this->record->validate('headers'));
    }

    #[Depends('testFailedValidate')]
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
