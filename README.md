# Yii2 Guzzle http log

Library for storing http queries into database

## Installation
```bash
composer require wearesho-team/yii2-guzzle
```

## Usage
1. Append [Bootstrap](./src/Bootstrap.php) to your application
```php
<?php

use Wearesho\Yii\Guzzle;

return [
    'bootstrap' => [
        'http-log' => [
            'class' => Guzzle\Bootstrap::class,
            // Add regular expression if you need exclude their from logging
            'exclude' => ['/^.*(google).*$/iu'],
            // Guzzle client configuration settings
            'config' => [
                'timeout' => 10,
            ],
        ],
    ],
];
```
2. Use `\Yii::$container` to instantiate [GuzzleHttp\Client](http://docs.guzzlephp.org) and send requests to log them
3. Use [Guzzle\Log\Request](./src/Log/Request.php), [Guzzle\Log\Response](./src/Log/Response.php), [Guzzle\Log\Exception](./src/Log/Exception.php) to find logs

Note: for not UTF-8 request or response body (for example, files)
`(invalid UTF-8 bytes)` will be saved. 

## Contributors
- [Alexander <horat1us> Letnikow](mailto:reclamme@gmail.com)
- [Roman <KartaviK> Varkuta](mailto:roman.varkuta@gmail.com)

## License
[MIT](./LICENSE)
