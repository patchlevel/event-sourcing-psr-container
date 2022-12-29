<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Factory;

use Patchlevel\EventSourcing\Aggregate\AggregateRoot;
use Patchlevel\EventSourcing\Metadata\AggregateRoot\AggregateRootRegistry;
use Patchlevel\EventSourcing\Metadata\AggregateRoot\AttributeAggregateRootRegistryFactory;
use Psr\Container\ContainerInterface;

/**
 * @psalm-type Config = array{
 *     paths: list<string>,
 *     classes: array<string, class-string<AggregateRoot>>,
 * }
 *
 * @extends Factory<Config>
 */
final class AggregateRootRegistryFactory extends Factory
{
    public function __invoke(ContainerInterface $container): AggregateRootRegistry
    {
        $config = $this->sectionConfig($container);

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

    protected function section(): string
    {
        return 'aggregate';
    }
}
