<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Factory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Psr\Container\ContainerInterface;

/**
 * @psalm-type Config = array{
 *     url: string
 * }
 *
 * @extends Factory<Config>
 */
final class ConnectionFactory extends Factory
{
    public const SERVICE_NAME = 'event_sourcing.connection';

    public function __invoke(ContainerInterface $container): Connection
    {
        $config = $this->sectionConfig($container);

        return DriverManager::getConnection([
            'url' => $config['url'],
        ]);
    }

    protected function section(): string
    {
        return 'connection';
    }
}
