<?php

namespace Wearesho\Yii\Guzzle\Log;

use yii\db;
use yii\behaviors;
use Horat1us\Yii\Exceptions\ModelException;
use Carbon\Carbon;

/**
 * Class Exception
 * @package Wearesho\Yii\Guzzle\Log
 *
 * @property string $http_request_id [integer]
 * @property string $type
 * @property array $trace [jsonb] Exception Trace Format
 * @property int $created_at [timestamp(0)]
 * @property-read Request $request
 */
class Exception extends db\ActiveRecord
{
    public static function tableName(): string
    {
        return 'http_exception';
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
            [['http_request_id', 'type', 'trace',], 'required',],
            [['http_request_id',], 'exist', 'targetRelation' => 'request',],
            [['http_request_id',], 'unique',],
            [['type',], 'string',],
            [['trace',], 'safe',],
        ];
    }

    public function getRequest(): db\ActiveQuery
    {
        return $this->hasOne(Request::class, ['id' => 'http_request_id']);
    }

    public function setRequest(Request $request = null): Exception
    {
        $this->http_request_id = $request ? $request->id : null;
        $this->populateRelation('request', $request);
        return $this;
    }

    public static function create(\Throwable $exception, Request $logRequest): Exception
    {
        $logException = new static([
            'type' => get_class($exception),
            'trace' => $exception->getTrace(),
            'request' => $logRequest,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        ModelException::saveOrThrow($logException);

        return $logException;
    }
}
