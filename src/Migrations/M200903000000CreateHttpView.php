<?php

namespace Wearesho\Yii\Guzzle\Migrations;

use Yii;
use yii\db\Migration;

class M200903000000CreateHttpView extends Migration
{
    private const VIEW_NAME = 'http_view';

    public function safeUp(): void
    {
        $this->getDb()
            ->createCommand(<<<SQL
create view http_view as
select req.uri,
       req.method,
       req.headers,
       req.body,
       res.status,
       res.headers    as response_headers,
       res.body       as response_body,
       req.created_at as request_at,
       res.created_at as response_at
from http_request req
         inner join http_response res on res.http_request_id = req.id
order by req.id desc
SQL
            )
            ->execute();
        $this->createIndex(
            'i_' . static::VIEW_NAME,
            static::VIEW_NAME,
            'uri'
        );
    }

    public function safeDown(): void
    {
        Yii::$app->db
            ->createCommand(
                'drop view http_view;'
            )
            ->execute();
    }
}
