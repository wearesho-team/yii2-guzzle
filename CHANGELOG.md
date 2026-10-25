# Yii2 Guzzle Changelog

## Release 2.1
- Requires PHP >= 8.4 (was ^7.4 | ^8.0); tested on PHP 8.4 and 8.5.
- Requires the first dependency releases that support PHP 8.4/8.5: guzzlehttp/guzzle ^7.8.2,
yiisoft/yii2 ^2.0.54, yiisoft/yii2-queue ^2.3.8, horat1us/yii2-carbon-behavior ^1.4,
horat1us/yii2-migration-bootstrap ^1.4, horat1us/yii2-validation-exception ^1.2.
- Explicit nullable `$request` parameter in [Log\Response::setRequest()](./src/Log/Response.php)
and [Log\Exception::setRequest()](./src/Log/Exception.php) (implicitly nullable parameters are deprecated since PHP 8.4).

## Release 2.0
- Added RepositoryInterface to implement different log saving options.
- Added [SyncRepository](./src/Log/SyncRepository.php) to save as in version 1.0
- Added [QueueRepository](./src/Log/QueueRepository.php) to save through a queue to bypass
the database transaction problem repository implementations
- Added the repository property in [Bootstrap](./src/Bootstrap.php) to configure the repository.
- Static create methods from Request, Response, Exception replaced with [Factory](./src/Log/Factory.php) class
