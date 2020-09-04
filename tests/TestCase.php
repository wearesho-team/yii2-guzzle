<?php declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Tests;

use Horat1us\Yii\PHPUnit;
use yii\helpers;

class TestCase extends PHPUnit\TestCase
{
    protected function setUp(): void
    {
        \Yii::setAlias('@configFile', __DIR__ . DIRECTORY_SEPARATOR . 'config.php');
        parent::setUp();
    }

    public function globalFixtures(): array
    {
        $fixtures = [
            [
                'class' => PHPUnit\MigrateFixture::class,
                'migrationNamespaces' => [
                    'Wearesho\\Yii\\Guzzle\\Migrations',
                ],
            ]
        ];

        return helpers\ArrayHelper::merge(parent::globalFixtures(), $fixtures);
    }
}
