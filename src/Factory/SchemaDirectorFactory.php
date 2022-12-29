<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Factory;

use Patchlevel\EventSourcing\Schema\ChainSchemaConfigurator;
use Patchlevel\EventSourcing\Schema\DoctrineSchemaDirector;
use Patchlevel\EventSourcing\Schema\SchemaDirector;
use Patchlevel\EventSourcing\Store\Store;
use Psr\Container\ContainerInterface;

final class SchemaDirectorFactory extends Factory
{
    protected function createWithConfig(ContainerInterface $container): SchemaDirector
    {
        return new DoctrineSchemaDirector(
            $this->retrieveDependency(
                $container,
                'event_sourcing.connection',
                new ConnectionFactory()
            ),
            new ChainSchemaConfigurator([
                $this->retrieveDependency(
                    $container,
                    Store::class,
                    new StoreFactory()
                ),
            ]),
        );
    }
}
