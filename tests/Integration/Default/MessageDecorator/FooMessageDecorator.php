<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Tests\Integration\Default\MessageDecorator;

use Patchlevel\EventSourcing\EventBus\Decorator\MessageDecorator;
use Patchlevel\EventSourcing\EventBus\Message;

final class FooMessageDecorator implements MessageDecorator
{
    public function __invoke(Message $message): Message
    {
        return $message->withCustomHeader('foo', 'bar');
    }
}
