<?php declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Migrations;

use yii\db;

class M200903000001CreateHttpRequestUrlIndex extends db\Migration
{
    private const TABLE_NAME = 'http_request';
    private const INDEX_NAME = 'i_' . self::TABLE_NAME . '_uri';

    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        if ($this->db->driverName === 'mysql') {
            $this->execute(/** @lang MySQL */ "CREATE INDEX " . static::INDEX_NAME . " ON "
                . static::TABLE_NAME . "(uri(200))");
        } else {
            $this->createIndex(
                self::INDEX_NAME,
                static::TABLE_NAME,
                'uri'
            );
        }
    }

    public function safeDown()
    {
        if ($this->db->driverName === 'mysql') {
            $this->execute(/** @lang MySQL */ "DROP INDEX " . static::INDEX_NAME . " ON " . static::TABLE_NAME);
        } else {
            $this->execute("DROP INDEX " . static::INDEX_NAME);
        }
    }
}
