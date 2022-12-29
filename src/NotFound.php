<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer;

use Psr\Container\NotFoundExceptionInterface;

final class NotFound extends ContainerException implements NotFoundExceptionInterface
{
}
