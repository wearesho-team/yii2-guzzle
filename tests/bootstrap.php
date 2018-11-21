<?php

use Dotenv\Dotenv;

$dotEnv = new Dotenv(dirname(__DIR__));
$dotEnv->load();

getenv('DB_PATH') || putenv("DB_PATH=" . __DIR__ . '/db.sqlite');


\Yii::setAlias(
    '@Wearesho/Yii/Guzzle',
    dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src'
);

Yii::setAlias('@runtime', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'runtime');

\Yii::setAlias('@configFile', __DIR__ . DIRECTORY_SEPARATOR . 'config.php');

Yii::setAlias('@fileStorage', Yii::getAlias('@runtime'));

Yii::setAlias('@output', __DIR__ . DIRECTORY_SEPARATOR . 'output');
