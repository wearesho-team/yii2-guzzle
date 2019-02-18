<?php

namespace Wearesho\Yii\Guzzle\Tests\Unit;

use GuzzleHttp;
use Wearesho\Yii\Guzzle\Bootstrap;
use Wearesho\Yii\Guzzle\Tests\TestCase;

/**
 * Class BootstrapTest
 * @package Wearesho\Yii\Guzzle\Tests\Unit
 * @coversDefaultClass \Wearesho\Yii\Guzzle\Bootstrap
 * @internal
 */
class BootstrapTest extends TestCase
{
    protected $aliases;

    protected function setUp()
    {
        parent::setUp();

        $this->aliases = \Yii::$aliases;
    }

    protected function tearDown()
    {
        parent::tearDown();

        \Yii::$aliases = $this->aliases;
    }

    public function testBootstrapApp(): void
    {
        $bootstrap = new Bootstrap([
            'exclude' => ['/php.net/'],
            'config' => [
                'timeout' => 10,
            ],
        ]);
        $bootstrap->bootstrap($this->app);
        $this->assertEquals(
            \Yii::getAlias('@vendor/wearesho-team/yii2-guzzle/src'),
            \Yii::getAlias('@Wearesho/Yii/Guzzle')
        );

        /** @var GuzzleHttp\Client $client */
        $client = \Yii::$container->get(GuzzleHttp\ClientInterface::class);
        $this->assertInstanceOf(
            GuzzleHttp\Client::class,
            $client
        );
        $this->assertEquals(
            10,
            $client->getConfig('timeout')
        );
    }
}
