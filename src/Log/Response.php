<?php

namespace Wearesho\Yii\Guzzle\Log;

use yii\db;
use yii\behaviors;
use Horat1us\Yii\Exceptions\ModelException;
use Psr\Http\Message\ResponseInterface;
use Carbon\Carbon;

/**
 * Class Response
 * @package Wearesho\Yii\Guzzle\Log
 * @property string $http_request_id [integer]
 * @property int $status [smallint]
 * @property string $headers [jsonb]
 * @property string $body
 * @property int $created_at [timestamp(0)]
 *
 * @property-read Request $request
 */
class Response extends db\ActiveRecord
{
    public static function tableName(): string
    {
        return 'http_response';
    }

    public static function primaryKey()
    {
        return ['http_request_id'];
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
            [['http_request_id', 'status', 'headers',], 'required',],
            [['http_request_id',], 'exist', 'targetRelation' => 'request',],
            [['http_request_id',], 'unique',],
            [['status',], 'integer', 'min' => 100, 'max' => 599,],
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
        $logResponse = new static([
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => (string)$response->getBody(),
            'request' => $logRequest,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        ModelException::saveOrThrow($logResponse);

        return $logResponse;
    }
}
