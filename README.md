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
             // Add plain string of domains if you need exclude their from logging
             'excludedDomains' => ['http://exexample.com',],
              // Add regular expression if you need exclude their from logging
             'excludedDomainsRegexes' => ['/^.*(google).*$/iu'],
         ],
    ],
];
```
2. Use `\Yii::$container` to instantiate [GuzzleHttp\Client](http://docs.guzzlephp.org) and send requests to log them
3. Use [Guzzle\Log\Request](./src/Log/Request.php), [Guzzle\Log\Response](./src/Log/Response.php), [Guzzle\Log\Exception](./src/Log/Exception.php) to find logs

## Contributors
- [Alexander <horat1us> Letnikow](mailto:reclamme@gmail.com)
- [Roman <KartaviK> Varkuta](mailto:roman.varkuta@gmail.com)

## License
[MIT](./LICENSE)
