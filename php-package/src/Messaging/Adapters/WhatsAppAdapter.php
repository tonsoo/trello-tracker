<?php

namespace Tonso\TrelloTracker\Messaging\Adapters;

use Tonso\TrelloTracker\Messaging\Contracts\MessagingAdapter;

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

                    $messages[] = [
                        'external_id' => $message['id'] ?? null,
                        'text' => $message['text']['body'] ?? '',
                        'source' => 'whatsapp',
                        'processed' => false,
                    ];
                }
            }
        }

        return $messages;
    }
}
