<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Factory;

use Patchlevel\EventSourcing\Metadata\AggregateRoot\AggregateRootRegistry;
use Patchlevel\EventSourcing\Metadata\AggregateRoot\AttributeAggregateRootRegistryFactory;
use Psr\Container\ContainerInterface;

use function assert;

/**
 * @psalm-type Config = array{
 *     paths: list<string>,
 *     classes: list<string>,
 * }
 */
final class AggregateRootRegistryFactory extends Factory
{
    protected function createWithConfig(ContainerInterface $container): AggregateRootRegistry
    {
        $config = $this->retrieveConfig($container, 'aggregate');
        assert($config instanceof Config);

        $aggregateRootRegistry = new AggregateRootRegistry($config['classes']);

        if ($config['paths'] !== []) {
            $aggregateRootRegistry = (new AttributeAggregateRootRegistryFactory())->create($config['paths']);
        }

        return $aggregateRootRegistry;
    }

    /**
     * @return Config
     */
    protected function defaultConfig(): array
    {
        return [
            'paths' => [],
            'classes' => [],
        ];
    }
}
