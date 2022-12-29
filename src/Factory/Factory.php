<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Factory;

use Psr\Container\ContainerInterface;
use RuntimeException;

use function array_replace_recursive;
use function is_array;

/**
 * @template C as array
 */
abstract class Factory
{
    final public function __construct()
    {
    }

    abstract public function __invoke(ContainerInterface $container): mixed;

    protected function section(): ?string
    {
        return null;
    }

    /**
     * @return C
     */
    protected function defaultConfig(): array
    {
        return [];
    }

    /**
     * @return C
     */
    protected function sectionConfig(ContainerInterface $container): array
    {
        $section = $this->section();

        if ($section === null) {
            return $this->defaultConfig();
        }

        $applicationConfig = $container->has('config') ? $container->get('config') : [];

        if (!is_array($applicationConfig)) {
            throw new RuntimeException('wrong config');
        }

        $sectionConfig = $applicationConfig['event_sourcing'][$section] ?? [];

        if (!is_array($sectionConfig)) {
            throw new RuntimeException('wrong config');
        }

        return array_replace_recursive($this->defaultConfig(), $sectionConfig);
    }

    /**
     * @param string|class-string<T> $service
     *
     * @return ($service is class-string ? T : mixed)
     *
     * @template T
     */
    protected function retrieveDependency(
        ContainerInterface $container,
        string $service,
        Factory $factory,
    ): object {
        if ($container->has($service)) {
            return $container->get($service);
        }

        return (new $factory())($container);
    }
}
