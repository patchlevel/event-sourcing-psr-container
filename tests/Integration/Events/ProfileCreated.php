<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Tests\Integration\Events;

use Patchlevel\EventSourcing\Attribute\Event;
use Patchlevel\EventSourcing\Attribute\Normalize;
use Patchlevel\EventSourcingPsrContainer\Tests\Integration\Normalizer\ProfileIdNormalizer;
use Patchlevel\EventSourcingPsrContainer\Tests\Integration\ProfileId;

#[Event('profile.created')]
final class ProfileCreated
{
    public function __construct(
        #[Normalize(new ProfileIdNormalizer())]
        public ProfileId $profileId,
        public string $name
    ) {
    }
}
