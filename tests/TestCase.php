<?php

namespace Wearesho\Yii\Guzzle\Tests;

use Wearesho\Yii\Guzzle\Bootstrap;
use yii\helpers;
use yii\phpunit;

/**
 * Class TestCase
 * @package Wearesho\Yii\Guzzle\Tests
 * @internal
 */
class TestCase extends phpunit\TestCase
{
    public function globalFixtures(): array
    {
        $fixtures = [
            [
                'class' => phpunit\MigrateFixture::class,
                'migrationNamespaces' => [
                    'Wearesho\\Yii\\Guzzle\\Migrations',
                ],
            ]
        ];

        return helpers\ArrayHelper::merge(parent::globalFixtures(), $fixtures);
    }
}
