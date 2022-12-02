# Yii2 Guzzle http log
[![Test & Lint](https://github.com/wearesho-team/yii2-guzzle/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/wearesho-team/yii2-guzzle/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/wearesho-team/yii2-guzzle/v/stable.png)](https://packagist.org/packages/wearesho-team/yii2-guzzle)
[![Total Downloads](https://poser.pugx.org/wearesho-team/yii2-guzzle/downloads.png)](https://packagist.org/packages/wearesho-team/yii2-guzzle)
[![codecov](https://codecov.io/gh/wearesho-team/yii2-guzzle/branch/master/graph/badge.svg)](https://codecov.io/gh/wearesho-team/yii2-guzzle)

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
use Psr\Http\Message\RequestInterface;

return [
    'bootstrap' => [
        'http-log' => [
            'class' => Guzzle\Bootstrap::class,
            // Logs exclude rules
            'exclude' => [
                // url regular expression
                '/^.*(google).*$/iu',
                // or closure (return true if you don't need to log request)
                fn(Message\RequestInterface $request): bool => $request->getUri()->getHost() === 'zsu.gov.ua/'
            ],
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
