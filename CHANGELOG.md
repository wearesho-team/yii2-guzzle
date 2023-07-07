# Yii2 Guzzle Changelog

## Release 2.0
- Added RepositoryInterface to implement different log saving options.
- Added [SyncRepository](./src/Log/SyncRepository.php) to save as in version 1.0
- Added [QueueRepository](./src/Log/QueueRepository.php) to save through a queue to bypass
the database transaction problem repository implementations
- Added the repository property in [Bootstrap](./src/Bootstrap.php) to configure the repository.
- Static create methods from Request, Response, Exception replaced with [Factory](./src/Log/Factory.php) class
