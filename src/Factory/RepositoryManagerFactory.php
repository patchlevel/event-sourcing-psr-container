<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Factory;

use Patchlevel\EventSourcing\EventBus\EventBus;
use Patchlevel\EventSourcing\Metadata\AggregateRoot\AggregateRootRegistry;
use Patchlevel\EventSourcing\Repository\DefaultRepositoryManager;
use Patchlevel\EventSourcing\Repository\RepositoryManager;
use Patchlevel\EventSourcing\Store\Store;
use Psr\Container\ContainerInterface;

/**
 * @extends Factory<array>
 */
final class RepositoryManagerFactory extends Factory
{
    public function __invoke(ContainerInterface $container): RepositoryManager
    {
        return new DefaultRepositoryManager(
            $this->retrieveDependency(
                $container,
                AggregateRootRegistry::class,
                new AggregateRootRegistryFactory()
            ),
            $this->retrieveDependency(
                $container,
                Store::class,
                new StoreFactory()
            ),
            $this->retrieveDependency(
                $container,
                EventBus::class,
                new EventBusFactory()
            ),
        );
    }
}
