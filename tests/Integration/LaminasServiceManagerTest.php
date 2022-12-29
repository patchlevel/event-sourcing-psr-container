<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Tests\Integration;

use Laminas\ServiceManager\ServiceManager;
use Patchlevel\EventSourcing\Repository\RepositoryManager;
use Patchlevel\EventSourcing\Schema\SchemaDirector;
use Patchlevel\EventSourcingPsrContainer\ConfigBuilder;
use Patchlevel\EventSourcingPsrContainer\Factory\ConnectionFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\RepositoryManagerFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\SchemaDirectorFactory;
use Patchlevel\EventSourcingPsrContainer\Tests\Integration\Aggregate\Profile;
use Patchlevel\EventSourcingPsrContainer\Tests\Integration\Processor\SendEmailProcessor;
use Patchlevel\EventSourcingPsrContainer\Tests\Integration\Projection\ProfileProjection;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @coversNothing
 */
final class LaminasServiceManagerTest extends TestCase
{
    public function tearDown(): void
    {
        SendEmailMock::reset();
    }

    public function testSuccessful(): void
    {
        $config = (new ConfigBuilder())
            ->singleTable()
            ->databaseUrl('sqlite:///:memory:')
            ->addAggregatePath(__DIR__ . '/Aggregate')
            ->addEventPath(__DIR__ . '/Events')
            ->addProcessor(SendEmailProcessor::class)
            ->addProjector(ProfileProjection::class)
            ->build();

        $factories = [
            'event_sourcing.connection' => new ConnectionFactory(),
            //EventBus::class => new EventBusFactory(),
            //EventSerializer::class => new EventSerializerFactory(),
            //Store::class => new StoreFactory(),
            //AggregateRootRegistry::class => new AggregateRootRegistryFactory(),
            RepositoryManager::class => new RepositoryManagerFactory(),
            SchemaDirector::class => new SchemaDirectorFactory(),
            ProfileProjection::class => static fn (ContainerInterface $container) => new ProfileProjection($container->get('event_sourcing.connection')),
        ];

        $serviceManager = new ServiceManager([
            'services' => [
                'config' => ['event_sourcing' => $config],
                SendEmailProcessor::class => new SendEmailProcessor(),
            ],
            'factories' => $factories,
        ]);

        $repositoryManager = $serviceManager->get(RepositoryManager::class);

        $repository = $repositoryManager->get(Profile::class);
        $serviceManager->get(SchemaDirector::class)->create();
        $serviceManager->get(ProfileProjection::class)->create();

        $profile = Profile::create(ProfileId::fromString('1'), 'John');

        $repository->save($profile);

        $result = $serviceManager->get('event_sourcing.connection')
            ->fetchAssociative('SELECT * FROM projection_profile WHERE id = ?', ['1']);

        self::assertIsArray($result);
        self::assertArrayHasKey('id', $result);
        self::assertSame('1', $result['id']);
        self::assertSame('John', $result['name']);

        $profile = $repository->load('1');

        self::assertInstanceOf(Profile::class, $profile);
        self::assertSame('1', $profile->aggregateRootId());
        self::assertSame(1, $profile->playhead());
        self::assertSame('John', $profile->name());
        self::assertSame(1, SendEmailMock::count());
    }
}
