<?php

declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Tests;

use Wearesho\Yii\Guzzle;
use yii\console;
use yii\db;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    private ?string $migrationNamespace = null;
    private ?string $bootstrapClass = Guzzle\Bootstrap::class;

    /**
     * @var db\Migration[]
     */
    private array $migrations = [];

    protected function setUp(): void
    {
        parent::setUp();

        $dsn = getenv('DB_TYPE')
            . ":host=" . getenv("DB_HOST")
            . ";port=" . getenv("DB_PORT")
            . ";dbname=" . getenv("DB_NAME");

        $config = [
            'id' => 'package-test',
            'basePath' => dirname(__DIR__),
            'components' => [
                'db' => [
                    'class' => db\Connection::class,
                    'dsn' => $dsn,
                    'username' => getenv('DB_USER'),
                    'password' => getenv('DB_PASS') ?: '',
                ],
            ],
            'bootstrap' => [
                $this->getBootstrapClass(),
            ],
        ];

        \Yii::$app = new console\Application($config);

        $this->migrations = [];
        // need to transform app package alias to real app alias
        $migrationsDir = str_replace(
            'vendor/' . $this->getPackageName() . '/',
            '',
            \Yii::getAlias('@' . str_replace('\\', '/', $this->getMigrationNamespace()))
        );

        foreach (scandir($migrationsDir) as $file) {
            if (is_dir($migrationsDir . DIRECTORY_SEPARATOR . $file)) {
                continue;
            }
            $class = $this->getMigrationNamespace() . '\\' . str_replace('.php', '', $file);

            $migration = new $class();
            if (!$migration instanceof db\Migration) {
                continue;
            }
            $migration->db = \Yii::$app->db;
            $this->migrations[] = $migration;

            ob_start();
            if ($migration->up() === false) {
                ob_end_flush();
                throw new \Exception("Migration failed.");
            }
            ob_end_clean();
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        /** @var db\Migration $migration */
        foreach (array_reverse($this->migrations) as $migration) {
            ob_start();
            if ($migration->down() === false) {
                ob_end_flush();
                throw new \Exception("Migration failed.");
            }
            ob_end_clean();
        }
        \Yii::$app = null;
    }

    private function getPackageName(): string
    {
        $composerJsonPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'composer.json';
        $packageInfo = json_decode(
            file_get_contents($composerJsonPath),
            true,
            16,
            JSON_THROW_ON_ERROR
        );
        return $packageInfo['name'];
    }

    private function getMigrationNamespace(): string
    {
        return $this->migrationNamespace
            ?? str_replace('\\Tests', '\\Migrations', __NAMESPACE__);
    }

    private function getBootstrapClass(): string
    {
        return $this->bootstrapClass
            ?? str_replace('\\Tests', '\\Migrations\\Bootstrap', __NAMESPACE__);
    }
}
