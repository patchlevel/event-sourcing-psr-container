<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer;

use Patchlevel\EventSourcingPsrContainer\Factory\AggregateRootRegistryFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\ConnectionFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\EventBusFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\EventSerializerFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\ProjectorRepositoryFactory;
use Patchlevel\EventSourcingPsrContainer\Factory\StoreFactory;

/**
 * @psalm-import-type Config from AggregateRootRegistryFactory as AggregateConfig
 * @psalm-import-type Config from ConnectionFactory as ConnectionConfig
 * @psalm-import-type Config from EventBusFactory as EventBusConfig
 * @psalm-import-type Config from EventSerializerFactory as EventConfig
 * @psalm-import-type Config from ProjectorRepositoryFactory as ProjectionConfig
 * @psalm-import-type Config from StoreFactory as StoreConfig
 *
 * @psalm-type Config = array{
 *     event_bus: EventBusConfig,
 *     projection: ProjectionConfig,
 *     connection: ConnectionConfig,
 *     store: StoreConfig,
 *     aggregate: AggregateConfig,
 *     event: EventConfig
 * }
 */
final class ConfigBuilder
{
    /** @var non-empty-string|null */
    private ?string $databaseUrl = null;
    private string $type = 'single';

    /** @var list<non-empty-string> */
    private array $aggregates = [];

    /** @var list<non-empty-string> */
    private array $events = [];

    /** @var list<non-empty-string> */
    private array $projectors = [];

    /** @var list<non-empty-string> */
    private array $processors = [];

    /**
     * @param non-empty-string $url
     *
     * @return $this
     */
    public function databaseUrl(string $url): self
    {
        $this->databaseUrl = $url;

        return $this;
    }

    /**
     * @return $this
     */
    public function singleTable(): self
    {
        $this->type = 'single';

        return $this;
    }

    /**
     * @return $this
     */
    public function multiTable(): self
    {
        $this->type = 'multi';

        return $this;
    }

    /**
     * @param non-empty-string $path
     *
     * @return $this
     */
    public function addAggregatePath(string $path): self
    {
        $this->aggregates[] = $path;

        return $this;
    }

    /**
     * @param non-empty-string $path
     *
     * @return $this
     */
    public function addEventPath(string $path): self
    {
        $this->events[] = $path;

        return $this;
    }

    /**
     * @param non-empty-string $serviceId
     *
     * @return $this
     */
    public function addProjector(string $serviceId): self
    {
        $this->projectors[] = $serviceId;

        return $this;
    }

    /**
     * @param non-empty-string $serviceId
     *
     * @return $this
     */
    public function addProcessor(string $serviceId): self
    {
        $this->processors[] = $serviceId;

        return $this;
    }

    /**
     * @return Config
     */
    public function build(): array
    {
        return [
            'connection' => [
                'url' => $this->databaseUrl,
            ],
            'store' => [
                'type' => $this->type,
            ],
            'aggregate' => [
                'paths' => $this->aggregates,
            ],
            'event' => [
                'paths' => $this->events,
            ],
            'event_bus' => [
                'listeners' => $this->processors,
            ],
            'projection' => [
                'projectors' => $this->projectors
            ],
        ];
    }
}
