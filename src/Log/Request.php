<?php

namespace Wearesho\Yii\Guzzle\Log;

use yii\db;
use yii\behaviors;
use Carbon\Carbon;
use Horat1us\Yii\Exceptions\ModelException;
use Psr\Http\Message\RequestInterface;

/**
 * Class Request
 * @package Wearesho\Yii\Guzzle\Log
 * @property string $id [integer]
 * @property string $method [varchar(6)]
 * @property string $uri
 * @property array $headers [jsonb] Key is header name and value is array of string values
 * @property string $body
 * @property int $created_at [timestamp(0)]
 */
class Request extends db\ActiveRecord
{
    public static function tableName(): string
    {
        return 'http_request';
    }

    public function behaviors(): array
    {
        return [
            'ts' => [
                'class' => behaviors\TimestampBehavior::class,
                'updatedAtAttribute' => false,
                'value' => function (): string {
                    return Carbon::now()->toDateTimeString();
                },
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['method', 'uri', 'headers', 'body',], 'required',],
            [['method',], 'string', 'max' => 6,],
            [['method',], 'filter', 'filter' => 'mb_strtoupper',],
            [['uri',], 'string',],
            [['headers',], 'each', 'rule' => ['each', 'rule' => 'string',],],
            [['body',], 'string',],
        ];
    }

    public static function create(RequestInterface $request): Request
    {
        $logRequest = new static([
            'method' => $request->getMethod(),
            'uri' => (string)$request->getUri(),
            'headers' => $request->getHeaders(),
            'body' => (string)$request->getBody(),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        ModelException::saveOrThrow($logRequest);

        return $logRequest;
    }
}
