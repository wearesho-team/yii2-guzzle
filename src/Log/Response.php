<?php

declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Log;

use Psr\Http\Message\ResponseInterface;
use Horat1us\Yii\CarbonBehavior;
use Horat1us\Yii\Validation;
use yii\db;

/**
 * Class Response
 * @package Wearesho\Yii\Guzzle\Log
 * @property string $http_request_id [integer]
 * @property int $status [smallint]
 * @property string $headers [jsonb]
 * @property string $body
 * @property int $created_at [timestamp(0)]
 * @property-read Request $request
 */
class Response extends db\ActiveRecord
{
    public const NOT_UTF_8_BODY = Request::NOT_UTF_8_BODY;

    public static function tableName(): string
    {
        return 'http_response';
    }

    public static function primaryKey(): array
    {
        return ['http_request_id'];
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
            [['http_request_id', 'status',], 'required',],
            [['http_request_id',], 'exist', 'targetRelation' => 'request',],
            [['http_request_id',], 'unique',],
            [['status',], 'integer', 'min' => 100, 'max' => 599,],
            [['headers',], 'default', 'value' => [],],
            [['headers',], 'each', 'rule' => ['each', 'rule' => ['string'],],],
            [['body',], 'string',],
        ];
    }

    public function getRequest(): db\ActiveQuery
    {
        return $this->hasOne(Request::class, ['id' => 'http_request_id']);
    }

    public function setRequest(Request $request = null): Response
    {
        $this->http_request_id = $request ? $request->id : null;
        $this->populateRelation('request', $request);
        return $this;
    }

    public static function create(ResponseInterface $response, Request $logRequest): Response
    {
        $body = (string)$response->getBody();
        if (!mb_check_encoding($body, "UTF-8")) {
            $body = static::NOT_UTF_8_BODY;
        }

        $logResponse = new static([
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => $body,
            'request' => $logRequest,
        ]);

        Validation\Exception::saveOrThrow($logResponse);

        return $logResponse;
    }
}
