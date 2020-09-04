<?php

namespace Wearesho\Yii\Guzzle\Tests\Unit\Record;

use Wearesho\Yii\Guzzle;
use yii\db\ActiveRecord;

/**
 * Class TestCase
 * @package Wearesho\Yii\Guzzle\Tests\Unit\Record
 */
class TestCase extends Guzzle\Tests\TestCase
{
    /** @var ActiveRecord */
    protected $record = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->record = new $this->record();
    }
}
