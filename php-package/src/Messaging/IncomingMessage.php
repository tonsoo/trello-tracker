<?php

namespace Tonso\TrelloTracker\Messaging;

final class IncomingMessage
{
    public function __construct(
        public readonly string $platform,
        public readonly string $senderId,
        public readonly string $text,
        public readonly ?string $messageId = null,
        public readonly array $rawPayload = [],
    ) {}
}
