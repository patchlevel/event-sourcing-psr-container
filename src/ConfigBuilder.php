<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer;

/**
 * @psalm-type Config = array{
 *     event_bus: ?array{type: string, service: string},
 *     projection: array{projectionist: bool},
 *     watch_server: array{enabled: bool, host: string},
 *     connection: ?array{service: ?string, url: ?string},
 *     store: array{type: string, merge_orm_schema: bool},
 *     aggregates: list<string>,
 *     events: list<string>,
 *     snapshot_stores: array<string, array{type: string, service: string}>,
 *     migration: array{path: string, namespace: string},
 *     clock: array{freeze: ?string, service: ?string}
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
            'projectors' => $this->projectors,
        ];
    }
}
