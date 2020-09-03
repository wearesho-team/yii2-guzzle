<?php

namespace Wearesho\Yii\Guzzle\Migrations;

use yii\db\Migration;

/**
 * Class M181121100620CreateHttpRequestTable
 */
class M181121100620CreateHttpRequestTable extends Migration
{
    private const TABLE_NAME = 'http_request';
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('http_request', [
            'id' => $this->primaryKey(),
            'method' => $this->string(6)->notNull(),
            'uri' => $this->text()->notNull(),
            'headers' => $this->json()->notNull(),
            'body' => $this->text()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
        ]);
        $this->createIndex(
            'i_' . static::TABLE_NAME,
            static::TABLE_NAME,
            'uri'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('http_request');
    }
}
