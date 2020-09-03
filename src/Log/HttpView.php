<?php


namespace Wearesho\Yii\Guzzle\Log;

use yii\db;



/**
 * Class HttpView
 * @package Wearesho\Yii\Guzzle\Log
 * @property string $uri
 * @property string $method
 * @property $headers
 * @property string $body
 * @property integer $status
 * @property $response_headers
 * @property string $response_body
 * @property string $request_at
 * @property string $response_at

 */
class HttpView extends db\ActiveRecord
{

    public static function tableName(): string
    {
        return 'http_request';
    }


}