<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Factory;

use Patchlevel\EventSourcing\Projection\Projector\InMemoryProjectorRepository;
use Patchlevel\EventSourcing\Projection\Projector\Projector;
use Patchlevel\EventSourcing\Projection\Projector\ProjectorRepository;
use Psr\Container\ContainerInterface;

use function array_map;

/**
 * @psalm-type Config = array{
 *     projectors: list<string>
 * }
 *
 * @extends Factory<Config>
 */
final class ProjectorRepositoryFactory extends Factory
{
    public function __invoke(ContainerInterface $container): ProjectorRepository
    {
        $config = $this->sectionConfig($container);

        return new InMemoryProjectorRepository(
            array_map(static fn (string $id): Projector => $container->get($id), $config['projectors'])
        );
    }

    protected function section(): string
    {
        return 'projection';
    }
}
