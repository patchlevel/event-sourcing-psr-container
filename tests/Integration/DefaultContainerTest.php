<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Tests\Integration;

use Patchlevel\EventSourcingPsrContainer\ConfigBuilder;
use Patchlevel\EventSourcingPsrContainer\DefaultContainer;
use Patchlevel\EventSourcingPsrContainer\Tests\Integration\Aggregate\Profile;
use Patchlevel\EventSourcingPsrContainer\Tests\Integration\Processor\SendEmailProcessor;
use Patchlevel\EventSourcingPsrContainer\Tests\Integration\Projection\ProfileProjection;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
final class DefaultContainerTest extends TestCase
{
    public function tearDown(): void
    {
        SendEmailMock::reset();
    }

    public function testSuccessful(): void
    {
        $config = (new ConfigBuilder())
            ->singleTable()
            ->databaseUrl('sqlite:///:memory:')
            ->addAggregatePath(__DIR__ . '/Aggregate')
            ->addEventPath(__DIR__ . '/Events')
            ->addProcessor(SendEmailProcessor::class)
            ->addProjector(ProfileProjection::class)
            ->build();

        $container = new DefaultContainer(
            $config,
            [
                ProfileProjection::class => static fn (DefaultContainer $container) => new ProfileProjection($container->connection()),
                SendEmailProcessor::class => static fn () => new SendEmailProcessor(),
            ]
        );

        $repository = $container->repository(Profile::class);
        $container->schemaDirector()->create();
        $container->get(ProfileProjection::class)->create();

        $profile = Profile::create(ProfileId::fromString('1'), 'John');

        $repository->save($profile);

        $result = $container->connection()->fetchAssociative('SELECT * FROM projection_profile WHERE id = ?', ['1']);

        self::assertIsArray($result);
        self::assertArrayHasKey('id', $result);
        self::assertSame('1', $result['id']);
        self::assertSame('John', $result['name']);

        $profile = $repository->load('1');

        self::assertInstanceOf(Profile::class, $profile);
        self::assertSame('1', $profile->aggregateRootId());
        self::assertSame(1, $profile->playhead());
        self::assertSame('John', $profile->name());
        self::assertSame(1, SendEmailMock::count());
    }
}
