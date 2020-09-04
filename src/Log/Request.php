<?php declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Log;

use Horat1us\Yii\CarbonBehavior;
use yii\db;
use Psr\Http\Message\RequestInterface;
use Horat1us\Yii\Validation;

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
                'class' => CarbonBehavior::class,
                'updatedAtAttribute' => false,
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

        Validation\Exception::saveOrThrow($logRequest);

        return $logRequest;
    }
}
