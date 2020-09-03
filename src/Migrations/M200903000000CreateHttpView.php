<?php declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Migrations;

use yii\db;

class M200903000000CreateHttpView extends db\Migration
{
    /**
     * {@inheritdoc}
     */
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
    }

    public function safeDown(): void
    {
        $this->getDb()
            ->createCommand(
                'drop view http_view;'
            )
            ->execute();
    }
}
