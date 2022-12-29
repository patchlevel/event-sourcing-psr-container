[![Latest Stable Version](https://poser.pugx.org/patchlevel/event-sourcing-psr-container/v)](//packagist.org/packages/patchlevel/event-sourcing-psr-container)
[![License](https://poser.pugx.org/patchlevel/event-sourcing-psr-container/license)](//packagist.org/packages/patchlevel/event-sourcing-psr-container)

# Event-Sourcing PSR-11 Container

A lightweight but also all-inclusive event sourcing library with a focus on developer experience.

## Installation

```bash
composer require patchlevel/event-sourcing-psr-container
```

## Documentation

### Default Build-In Container

```php
use Patchlevel\EventSourcing\Container\ConfigBuilder;
use Patchlevel\EventSourcing\Container\DefaultContainer;
use Psr\Container\ContainerInterface;

$config = (new ConfigBuilder())
    ->singleTable()
    ->databaseUrl('mysql://user:secret@localhost/app')
    ->addAggregatePath('src/Domain/Hotel')
    ->addEventPath('src/Domain/Hotel/Event')
    ->addProjector(HotelProjection::class)
    ->addProcessor(SendCheckInEmailProcessor::class)
    ->build();
    
$container = new DefaultContainer(
    $config,
    [
        HotelProjection::class => fn(DefaultContainer $container) 
            => new HotelProjection($container->connection()),
        SendCheckInEmailProcessor::class => fn(DefaultContainer $container) 
            => new SendCheckInEmailProcessor($container->get('mailer')),
    ]
);

$hotelRepository = $container->repository(Hotel::class);
```

### Laminas Service Manager

```php
$config = (new ConfigBuilder())
    ->singleTable()
    ->databaseUrl('sqlite:///:memory:')
    ->addAggregatePath(__DIR__ . '/Aggregate')
    ->addEventPath(__DIR__ . '/Events')
    ->addProcessor(SendEmailProcessor::class)
    ->addProjector(ProfileProjection::class)
    ->build();

$serviceManager = new ServiceManager([
    'services' => [
        'config' => [
            'event_sourcing' => $config
        ],
        SendEmailProcessor::class => new SendEmailProcessor()
    ],
    'factories' => [
        'event_sourcing.connection' => new ConnectionFactory(),
        RepositoryManager::class => new RepositoryManagerFactory(),
        SchemaDirector::class => new SchemaDirectorFactory(),
        ProfileProjection::class => static fn (ContainerInterface $container) => new ProfileProjection($container->get('event_sourcing.connection')),
    ],
]);

$repositoryManager = $serviceManager->get(RepositoryManager::class);
```