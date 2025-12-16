# Configuração do WhatsApp Cloud API

Este guia ajuda a configurar o WhatsApp Cloud API para receber mensagens no seu endpoint e integrá-las ao fluxo do pacote.

## Passo a passo
- **Criar app na Meta** e habilitar o produto WhatsApp.
- **Obter credenciais**:
  - `phone_number_id` (ID do número)
  - Token de acesso (temporário ou permanente)
- **Configurar o webhook** para apontar para seu endpoint:
  - `POST /webhooks/messaging/whatsapp`
  - Verificação: `GET /webhooks/messaging/whatsapp`
- **Verificação de webhook**:
  - A Meta enviará `hub.mode=subscribe`, `hub.verify_token`, `hub.challenge`.
  - Garanta que `WHATSAPP_SECRET` (no `.env`) coincida com o verify token configurado no app.
  - O controller `MessagingController@whatsappAuth` responde com `hub_challenge` quando válido.
- **Permissões e assinatura**:
  - Conceda permissão `messages`.
  - Assine o campo `messages` para o seu `phone_number_id` no app.
- **HTTPS acessível**:
  - Seu ambiente deve estar acessível por HTTPS público para receber callbacks.

## Variáveis de ambiente
Veja [`configuration.md`](configuration.md) e preencha no `.env`:
```env
WHATSAPP_TOKEN=seu_token_whatsapp
WHATSAPP_FROM_NUMBER=15551234567
WHATSAPP_FROM_ID=seu_phone_number_id
WHATSAPP_SECRET=seu_verify_token
```

## Fluxo de recebimento
- A Meta envia o payload ao `POST /webhooks/messaging/whatsapp`.
- O `WhatsAppAdapter` converte para `IncomingMessage[]` (apenas mensagens de texto).
- Cada mensagem é processada via fila por `ProcessIncomingMessageJob`.
- O uso do `AiIntentAnalyzer` e `TrelloOrchestrator` está detalhado em:
  - [`docs/ai.md`](ai.md)
  - [`docs/trello.md`](trello.md)
  - [`docs/lifecycle.md`](lifecycle.md)

## Debug e testes
- Verifique logs de recebimento no `MessagingController`.
- Para testar rapidamente, envie um cURL simulando o callback (ajuste o host):
```bash
curl -X POST "https://seu-host.com/webhooks/messaging/whatsapp" \
  -H "Content-Type: application/json" \
  -d '{
    "entry": [
      {
        "changes": [
          {
            "value": {
              "messages": [
                {
                  "from": "5511999999999",
                  "id": "wamid.HBgM...",
                  "type": "text",
                  "text": { "body": "App trava ao abrir relatório" }
                }
              ]
            }
          }
        ]
      }
    ]
  }'
```

## Referências
- [`http-webhooks.md`](http-webhooks.md)
- [`adapters.md`](adapters.md)
