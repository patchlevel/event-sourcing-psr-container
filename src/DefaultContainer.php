<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer;

use Doctrine\DBAL\Connection;
use Patchlevel\EventSourcing\Aggregate\AggregateRoot;
use Patchlevel\EventSourcing\EventBus\EventBus;
use Patchlevel\EventSourcing\Metadata\AggregateRoot\AggregateRootRegistry;
use Patchlevel\EventSourcing\Projection\Projector\ProjectorRepository;
use Patchlevel\EventSourcing\Repository\Repository;
use Patchlevel\EventSourcing\Repository\RepositoryManager;
use Patchlevel\EventSourcing\Schema\SchemaDirector;
use Patchlevel\EventSourcing\Serializer\EventSerializer;
use Patchlevel\EventSourcing\Store\Store;
use Patchlevel\EventSourcingPsrContainer\Factory\AggregateRootRegistryFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\ConnectionFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\EventBusFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\EventSerializerFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\RepositoryManagerFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\SchemaDirectorFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\StoreFactory;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function array_merge;
use function is_callable;

/**
 * @psalm-import-type Config from ConfigBuilder
 */
final class DefaultContainer implements ContainerInterface
{
    /** @var array<string, mixed> */
    private array $definitions;

    /** @var array<string, mixed> */
    private array $resolvedEntries;

    /**
     * @param Config               $config
     * @param array<string, mixed> $definitions
     */
    public function __construct(array $config = [], array $definitions = [])
    {
        $this->resolvedEntries = [];

        $this->definitions = array_merge(
            [
                'config' => ['event_sourcing' => $config],
                'event_sourcing.connection' => new ConnectionFactory(),
                EventBus::class => new EventBusFactory(),
                EventSerializer::class => new EventSerializerFactory(),
                Store::class => new StoreFactory(),
                AggregateRootRegistry::class => new AggregateRootRegistryFactory(),
                RepositoryManager::class => new RepositoryManagerFactory(),
                SchemaDirector::class => new SchemaDirectorFactory(),
            ],
            $definitions,
            [
                ContainerInterface::class => $this,
                self::class => $this,
            ]
        );
    }

    /**
     * @param string|class-string<T> $id
     *
     * @return ($id is class-string ? T : mixed)
     *
     * @template T
     */
    public function get(string $id): mixed
    {
        if (!$this->has($id)) {
            throw new NotFound("No entry or class found for '$id'");
        }

        if (array_key_exists($id, $this->resolvedEntries)) {
            return $this->resolvedEntries[$id];
        }

        $value = $this->definitions[$id];

        if (is_callable($value)) {
            $value = $value($this);
        }

        $this->resolvedEntries[$id] = $value;

        return $value;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->definitions) || array_key_exists($id, $this->resolvedEntries);
    }

    public function connection(): Connection
    {
        return $this->get('event_sourcing.connection');
    }

    public function repositoryManager(): RepositoryManager
    {
        return $this->get(RepositoryManager::class);
    }

    /**
     * @param class-string<T> $aggregateClass
     *
     * @return Repository<T>
     *
     * @template T of AggregateRoot
     */
    public function repository(string $aggregateClass): Repository
    {
        return $this->repositoryManager()->get($aggregateClass);
    }

    public function store(): Store
    {
        return $this->get(Store::class);
    }

    public function schemaDirector(): SchemaDirector
    {
        return $this->get(SchemaDirector::class);
    }

    public function projectorRepository(): ProjectorRepository
    {
        return $this->get(ProjectorRepository::class);
    }

    public function aggregateRootRegistry(): AggregateRootRegistry
    {
        return $this->get(AggregateRootRegistry::class);
    }
}
