<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Factory;

use Patchlevel\EventSourcing\Serializer\DefaultEventSerializer;
use Patchlevel\EventSourcing\Serializer\EventSerializer;
use Psr\Container\ContainerInterface;

/**
 * @psalm-type Config = array{
 *     paths: list<string>
 * }
 *
 * @extends Factory<Config>
 */
final class EventSerializerFactory extends Factory
{
    public function __invoke(ContainerInterface $container): EventSerializer
    {
        $config = $this->sectionConfig($container);

        return DefaultEventSerializer::createFromPaths($config['paths']);
    }

    /**
     * @return Config
     */
    protected function defaultConfig(): array
    {
        return [
            'paths' => [],
        ];
    }

    protected function section(): string
    {
        return 'event';
    }
}
