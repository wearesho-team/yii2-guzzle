<?php

declare(strict_types=1);

namespace Wearesho\Yii\Guzzle\Log;

use Horat1us\Yii\CarbonBehavior;
use Horat1us\Yii\Validation;
use yii\db;

/**
 * Class Exception
 * @package Wearesho\Yii\Guzzle\Log
 *
 * @property string $http_request_id [integer]
 * @property string $type
 * @property array $trace [jsonb] Exception Trace Format
 * @property int $created_at [timestamp(0)]
 * @property Request $request
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
                'class' => CarbonBehavior::class,
                'updatedAtAttribute' => false,
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
        $trace = array_map(
            fn(array $data): array => array_intersect_key($data, array_flip([
                'file',
                'line',
                'function',
                'class',
            ])),
            $exception->getTrace()
        );
        $logException = new static([
            'type' => get_class($exception),
            'trace' => $trace,
            'request' => $logRequest,
        ]);

        Validation\Exception::saveOrThrow($logException);

        return $logException;
    }
}
