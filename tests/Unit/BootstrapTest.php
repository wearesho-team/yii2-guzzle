<?php

namespace Wearesho\Yii\Guzzle\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use GuzzleHttp;
use Wearesho\Yii\Guzzle\Bootstrap;
use Wearesho\Yii\Guzzle\Tests\TestCase;

/**
 * Class BootstrapTest
 * @package Wearesho\Yii\Guzzle\Tests\Unit
 * @internal
 */
#[CoversClass(\Wearesho\Yii\Guzzle\Bootstrap::class)]
class BootstrapTest extends TestCase
{
    public function testBootstrapApp(): void
    {
        $bootstrap = new Bootstrap([
            'exclude' => ['/php.net/'],
            'config' => [
                'timeout' => 10,
            ],
        ]);
        $bootstrap->bootstrap(\Yii::$app);

        /** @var GuzzleHttp\Client $client */
        $client = \Yii::$container->get(GuzzleHttp\ClientInterface::class);
        $this->assertInstanceOf(
            GuzzleHttp\Client::class,
            $client
        );
    }
}
