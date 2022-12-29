[![Latest Stable Version](https://poser.pugx.org/patchlevel/event-sourcing-psr-container/v)](//packagist.org/packages/patchlevel/event-sourcing-psr-container)
[![License](https://poser.pugx.org/patchlevel/event-sourcing-psr-container/license)](//packagist.org/packages/patchlevel/event-sourcing-psr-container)

# Event-Sourcing PSR-11 Container

[patchlevel/event-sourcing](https://github.com/patchlevel/event-sourcing) factories for PSR-11 containers.

## Installation

```bash
composer require patchlevel/event-sourcing-psr-container
```

## Documentation

### Config Builder

To create a configuration array, you can use the ConfigBuilder. 
This offers methods to adjust the configuration.

```php
$eventSourcingConfig = (new ConfigBuilder())
    ->singleTable()
    ->databaseUrl('mysql://user:secret@localhost/app')
    ->addAggregatePath(__DIR__ . '/Aggregate')
    ->addEventPath(__DIR__ . '/Events')
    ->addProcessor(SendEmailProcessor::class)
    ->addProjector(ProfileProjection::class)
    ->build();
```

### Default Build-In Container

The own PSR container implementation already integrates all necessary factories. 
So we only have to pass the configuration.

```php
use Patchlevel\EventSourcing\Container\ConfigBuilder;
use Patchlevel\EventSourcing\Container\DefaultContainer;

$container = new DefaultContainer(
    $eventSourcingConfig,
    [
        HotelProjection::class => fn(DefaultContainer $container) 
            => new HotelProjection($container->connection()),
        SendEmailProcessor::class => fn(DefaultContainer $container) 
            => new SendEmailProcessor($container->get('mailer')),
    ]
);

$container->get(SchemaDirector::class)->create();

$hotelRepository = $container->repository(Hotel::class);
```

### Laminas Service Manager

Factories can also be used with other PSR-11 compatible libraries. 
Here is an example with [Laminas](https://docs.laminas.dev/laminas-servicemanager/).

```bash
composer require laminas/laminas-servicemanager
```

We only have to specify the factories and pass the configuration.

```php
use Laminas\ServiceManager\ServiceManager;
use Patchlevel\EventSourcing\Repository\RepositoryManager;
use Patchlevel\EventSourcing\Schema\SchemaDirector;
use Patchlevel\EventSourcingPsrContainer\ConfigBuilder;
use Patchlevel\EventSourcingPsrContainer\Factory\ConnectionFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\RepositoryManagerFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\SchemaDirectorFactory;

$serviceManager = new ServiceManager([
    'services' => [
        'config' => [
            'event_sourcing' => $eventSourcingConfig
        ],
        SendEmailProcessor::class => new SendEmailProcessor()
    ],
    'factories' => [
        'event_sourcing.connection' => new ConnectionFactory(),
        RepositoryManager::class => new RepositoryManagerFactory(),
        SchemaDirector::class => new SchemaDirectorFactory(),
        HotelProjection::class => static fn (ServiceManager $container) => new HotelProjection($container->get('event_sourcing.connection')),
    ],
]);

$serviceManager->get(SchemaDirector::class)->create();

$repositoryManager = $serviceManager->get(RepositoryManager::class);
$hotelRepository = $repositoryManager->get(Hotel::class);
```