<?php

namespace Tonso\TrelloTracker\Messaging\Adapters;

use Tonso\TrelloTracker\Messaging\Contracts\MessagingAdapter;
use Tonso\TrelloTracker\Messaging\IncomingMessage;

final class WhatsAppAdapter implements MessagingAdapter
{
    public function parse(array $payload): array
    {
        $messages = [];

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];

                foreach ($value['messages'] ?? [] as $message) {
                    if (($message['type'] ?? null) !== 'text') {
                        continue;
                    }

                    $messages[] = new IncomingMessage(
                        platform: 'whatsapp',
                        senderId: $message['from'],
                        text: $message['text']['body'],
                        rawPayload: $message
                    );
                }
            }
        }

        return $messages;
    }
}
