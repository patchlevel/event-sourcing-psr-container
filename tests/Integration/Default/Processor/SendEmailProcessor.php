<?php

declare(strict_types=1);

namespace Patchlevel\EventSourcingPsrContainer\Tests\Integration\Default\Processor;

use Patchlevel\EventSourcing\EventBus\Listener;
use Patchlevel\EventSourcing\EventBus\Message;
use Patchlevel\EventSourcingPsrContainer\Tests\Integration\Default\Events\ProfileCreated;
use Patchlevel\EventSourcingPsrContainer\Tests\Integration\Default\SendEmailMock;

final class SendEmailProcessor implements Listener
{
    public function __invoke(Message $message): void
    {
        if (!$message->event() instanceof ProfileCreated) {
            return;
        }

        SendEmailMock::send();
    }
}
