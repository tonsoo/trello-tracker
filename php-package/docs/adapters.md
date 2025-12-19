# Adapters de Mensageria

Adapters traduzem payloads específicos da plataforma para DTOs `IncomingMessage`.

- Contrato: `Tonso\TrelloTracker\Messaging\Contracts\MessagingAdapter`
- Embutido: `Tonso\TrelloTracker\Messaging\Adapters\WhatsAppAdapter`
- DTO: `Tonso\TrelloTracker\Messaging\IncomingMessage`

## WhatsAppAdapter
- Itera `entry[].changes[].value.messages[]`
- Aceita apenas `type === 'text'`
- Produz `IncomingMessage(platform: 'whatsapp', senderId, text, rawPayload)`

## Criando um novo adapter
```php
namespace App\Messaging\Adapters;

use Tonso\TrelloTracker\Messaging\Contracts\MessagingAdapter;
use Tonso\TrelloTracker\Messaging\IncomingMessage;

final class TelegramAdapter implements MessagingAdapter
{
    public function parse(array $payload): array
    {
        // ...mapear o update do Telegram para IncomingMessage[]
        return [
            new IncomingMessage(
                platform: 'telegram',
                senderId: '123',
                text: '...',
                rawPayload: $payload,
            ),
        ];
    }
}
```

Faça o bind no seu Service Provider ou injete onde você processa o webhook.
