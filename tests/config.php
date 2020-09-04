<?php

return [
    'id' => 'yii2-guzzle',
    'basePath' => dirname(__DIR__),
    'components' => [
        'db' => [
            'class' => \yii\db\Connection::class,
            'dsn' => getenv('DB_DSN'),
            'username' => getenv("DB_USERNAME"),
            'password' => getenv("DB_PASSWORD") ?: null,
        ],
    ],
    'bootstrap' => [
        Wearesho\Yii\Guzzle\Migrations\Bootstrap::class,
    ]
];
