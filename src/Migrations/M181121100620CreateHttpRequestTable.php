<?php declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Migrations;

use yii\db;

/**
 * Class M181121100620CreateHttpRequestTable
 */
class M181121100620CreateHttpRequestTable extends db\Migration
{
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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('http_request');
    }
}
