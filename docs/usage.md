# Uso

## Endpoints de webhook
- GET `/webhooks/messaging/whatsapp` → verificação (responde com `hub_challenge` quando o token confere)
- POST `/webhooks/messaging/whatsapp` → eventos de mensagem

## Fluxo de processamento
1. `MessagingController@whatsapp` interpreta o payload via `WhatsAppAdapter`
2. Despacha `ProcessIncomingMessageJob` por mensagem
3. `ProcessIncomingMessage` → `AiIntentAnalyzer` → `StructuredIntent`
4. `TrelloOrchestrator` aplica regras de negócio

## Fila
- Garanta um worker rodando: `php artisan queue:work`
- `ProcessIncomingMessageJob` usa um lock curto com base no `messageId` (quando houver) para idempotência

## Enviar respostas (opcional)
- Use `Tonso\TrelloTracker\Services\WhatsappService::sendMessage($message, $to)` com seu `from.id` configurado
