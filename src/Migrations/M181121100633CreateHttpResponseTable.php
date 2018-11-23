<?php

namespace Wearesho\Yii\Guzzle\Migrations;

use yii\db\Migration;

/**
 * Class M181121100633CreateHttpResponseTable
 */
class M181121100633CreateHttpResponseTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable('http_response', [
            'http_request_id' => $this->integer(),
            'status' => $this->smallInteger(),
            'headers' => $this->json()->notNull(),
            'body' => $this->text()->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
        ]);

        $this->addPrimaryKey('pk_http_response', 'http_response', 'http_request_id');
        $this->addForeignKey(
            'fk_http_response_request',
            'http_response',
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
        $this->dropTable('http_response');
    }
}
