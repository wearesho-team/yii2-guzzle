<?php

if (file_exists(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env')) {
    $dotEnv = new \Dotenv\Dotenv(dirname(__DIR__));
    $dotEnv->load();
}

\Yii::setAlias(
    '@Wearesho/Yii/Guzzle',
    dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src'
);

Yii::setAlias('@runtime', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'runtime');

\Yii::setAlias('@configFile', __DIR__ . DIRECTORY_SEPARATOR . 'config.php');

Yii::setAlias('@fileStorage', Yii::getAlias('@runtime'));

Yii::setAlias('@output', __DIR__ . DIRECTORY_SEPARATOR . 'output');
