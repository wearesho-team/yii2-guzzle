<?php declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Migrations;

use yii\db;

class M200903000001CreateHttpRequestUrlIndex extends db\Migration
{
    private const TABLE_NAME = 'http_request';

    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        if ($this->db->driverName === 'mysql') {
            $this->execute(/** @lang MySQL */ `
CREATE INDEX i_http_request_uri
ON http_request (uri);`);
        } else {
            $this->createIndex(
                'i_' . static::TABLE_NAME . '_uri',
                static::TABLE_NAME,
                'uri'
            );
        }
    }
}
