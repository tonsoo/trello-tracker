# HTTP & Webhooks

## Rotas
Definidas em `routes/api.php` e carregadas automaticamente pelo Service Provider.

- `GET /webhooks/messaging/whatsapp` → `MessagingController@whatsappAuth`
- `POST /webhooks/messaging/whatsapp` → `MessagingController@whatsapp`

## Verificação
`whatsappAuth()` compara `hub_verify_token` com `config('trello-tracker.messaging.whatsapp.secret')` e devolve `hub_challenge` quando válido.

## Interpretação do payload
`whatsapp()` delega ao `WhatsAppAdapter`, que emite `IncomingMessage[]` apenas para mensagens de texto.
