<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Factory;

use Patchlevel\EventSourcing\EventBus\DefaultEventBus;
use Patchlevel\EventSourcing\EventBus\EventBus;
use Patchlevel\EventSourcing\EventBus\Listener;
use Patchlevel\EventSourcing\Projection\Projector\ProjectorRepository;
use Patchlevel\EventSourcing\Projection\Projector\SyncProjectorListener;
use Psr\Container\ContainerInterface;

use function array_map;

/**
 * @psalm-type Config = array{
 *     listeners: list<string>
 * }
 *
 * @extends Factory<Config>
 */
final class EventBusFactory extends Factory
{
    public function __invoke(ContainerInterface $container): EventBus
    {
        $config = $this->sectionConfig($container);

        $listeners = array_map(static fn (string $id): Listener => $container->get($id), $config['listeners']);

        $listeners[] = new SyncProjectorListener(
            $this->retrieveDependency(
                $container,
                ProjectorRepository::class,
                new ProjectorRepositoryFactory()
            )
        );

        return new DefaultEventBus($listeners);
    }

    /**
     * @return Config
     */
    protected function defaultConfig(): array
    {
        return [
            'listeners' => [],
        ];
    }

    protected function section(): string
    {
        return 'event_bus';
    }
}
