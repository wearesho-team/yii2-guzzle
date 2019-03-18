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
    public const NOT_UTF_8_BODY = "(invalid UTF-8 bytes)";

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
            [['method', 'uri',], 'required',],
            [['method',], 'string', 'max' => 6,],
            [['method',], 'filter', 'filter' => 'mb_strtoupper',],
            [['uri',], 'string',],
            [['headers',], 'default', 'value' => [],],
            [['headers',], 'each', 'rule' => ['each', 'rule' => ['string'],],],
            [['body',], 'string',],
        ];
    }

    public static function create(RequestInterface $request): Request
    {
        $body = (string)$request->getBody();
        if (!mb_check_encoding($body, 'UTF-8')) {
            $body = static::NOT_UTF_8_BODY;
        }

        $logRequest = new static([
            'method' => $request->getMethod(),
            'uri' => (string)$request->getUri(),
            'headers' => $request->getHeaders(),
            'body' => $body,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        ModelException::saveOrThrow($logRequest);

        return $logRequest;
    }
}
