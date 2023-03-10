<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Tests\Integration\Aggregate;

use Patchlevel\EventSourcing\Aggregate\AggregateRoot;
use Patchlevel\EventSourcing\Attribute\Aggregate;
use Patchlevel\EventSourcing\Attribute\Apply;
use Patchlevel\EventSourcing\Attribute\Normalize;
use Patchlevel\EventSourcing\Attribute\Snapshot;
use Patchlevel\EventSourcingPsrContainer\Tests\Integration\Events\ProfileCreated;
use Patchlevel\EventSourcingPsrContainer\Tests\Integration\Normalizer\ProfileIdNormalizer;
use Patchlevel\EventSourcingPsrContainer\Tests\Integration\ProfileId;

#[Aggregate('profile')]
#[Snapshot('default', 100)]
final class Profile extends AggregateRoot
{
    #[Normalize(new ProfileIdNormalizer())]
    private ProfileId $id;
    private string $name;

    public function aggregateRootId(): string
    {
        return $this->id->toString();
    }

    public static function create(ProfileId $id, string $name): self
    {
        $self = new self();
        $self->recordThat(new ProfileCreated($id, $name));

        return $self;
    }

    #[Apply(ProfileCreated::class)]
    protected function applyProfileCreated(ProfileCreated $event): void
    {
        $this->id = $event->profileId;
        $this->name = $event->name;
    }

    public function name(): string
    {
        return $this->name;
    }
}
