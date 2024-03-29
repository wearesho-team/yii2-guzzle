<?php

declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Migrations;

use yii\db;

/**
 * Class M181121100642CreateHttpExceptionTable
 */
class M181121100642CreateHttpExceptionTable extends db\Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('http_exception', [
            'http_request_id' => $this->integer()->notNull(),
            'type' => $this->text()->notNull(),
            'trace' => $this->json()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
        ]);

        $this->addPrimaryKey('pk_http_exception', 'http_exception', 'http_request_id');
        $this->addForeignKey(
            'fk_http_exception_request',
            'http_exception',
            'http_request_id',
            'http_request',
            'id',
            'cascade',
            'cascade'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown(): void
    {
        $this->dropTable('http_exception');
    }
}
