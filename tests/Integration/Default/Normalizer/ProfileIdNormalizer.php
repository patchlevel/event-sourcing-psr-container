<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Tests\Integration\Default\Normalizer;

use InvalidArgumentException;
use Patchlevel\EventSourcing\Serializer\Normalizer\Normalizer;
use Patchlevel\EventSourcingPsrContainer\Tests\Integration\Default\ProfileId;

use function is_string;

final class ProfileIdNormalizer implements Normalizer
{
    public function normalize(mixed $value): string
    {
        if (!$value instanceof ProfileId) {
            throw new InvalidArgumentException();
        }

        return $value->toString();
    }

    public function denormalize(mixed $value): ?ProfileId
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException();
        }

        return ProfileId::fromString($value);
    }
}
