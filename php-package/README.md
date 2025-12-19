# Trello Tracker

Um pacote Laravel que transforma mensagens recebidas do WhatsApp em cards acionáveis no Trello usando extração de intenção por IA. Ele escuta webhooks do WhatsApp, interpreta mensagens via adapters, classifica a intenção com um LLM e orquestra operações no Trello (criar/atualizar/arquivar cards, adicionar comentários, etc.).

## Recursos
- **Ingestão de webhooks do WhatsApp** via `routes/api.php` para `MessagingController`
- **Padrão Adapter** para plataformas de mensagens (`MessagingAdapter`), com `WhatsAppAdapter` embutido
- **Análise de intenção por IA** usando `OpenAI` através do contrato `LLMClient`
- **Orquestração inteligente do Trello** para deduplicar relatos e atualizar cards existentes
- **Publicação de config** e configuração por ambiente (`config/trello-tracker.php`)
- **Processamento em fila** com tratamento idempotente de mensagens recebidas

## Início Rápido
1. **Instalação**
```bash
composer require tonso/trello-tracker
```

2. **Publicar configuração**
```bash
php artisan vendor:publish --tag=trello-tracker-config
```

3. **Variáveis de ambiente** (veja `.env.example` e `docs/configuration.md`)
```env
WHATSAPP_TOKEN=...
WHATSAPP_FROM_NUMBER=...
WHATSAPP_FROM_ID=...
WHATSAPP_SECRET=...

TRELLO_KEY=...
TRELLO_TOKEN=...
TRELLO_BOARD_ID=...
TRELLO_LIST_ID=...

OPENAI_API_KEY=...
OPENAI_MODEL=gpt-4.1-mini
```

4. **Rotas de webhook**
- GET `/webhooks/messaging/whatsapp` para verificação
- POST `/webhooks/messaging/whatsapp` para eventos

5. **Worker da fila**
```bash
php artisan queue:work
```

## Arquitetura
- **Service Provider**: `src/TrelloTrackerServiceProvider.php`
  - Faz bind de `LLMClient` para `OpenAILLMClient`
  - Faz bind do cliente Trello e serviços (`TrelloService`, `TrelloOrchestrator`)
  - Registra `WhatsAppAdapter`
  - Carrega rotas e publica config

- **HTTP**: `routes/api.php` → `MessagingController`
  - Verifica webhook (`whatsappAuth()`)
  - Interpreta payload via `WhatsAppAdapter` e despacha `ProcessIncomingMessageJob`

- **Messaging**: contrato `MessagingAdapter` + `WhatsAppAdapter` + `IncomingMessage`

- **IA**: `AiIntentAnalyzer` + `LLMClient` (implementação OpenAI)

- **Caso de Uso**: `ProcessIncomingMessage` → extrai intenção → delega ao `TrelloOrchestrator`

- **Trello**: `TrelloService` (wrapper da API) + `TrelloOrchestrator` (regras de negócio) + objetos de valor em `src/Objects/Trello/`

## Ciclo (alto nível)
1. WhatsApp envia webhook → `MessagingController@whatsapp`
2. `WhatsAppAdapter` converte para `IncomingMessage[]`
3. Cada mensagem é enfileirada como `ProcessIncomingMessageJob` (lock idempotente por id da mensagem quando houver)
4. `ProcessIncomingMessage` usa `AiIntentAnalyzer` para obter `StructuredIntent`
5. `TrelloOrchestrator`:
   - `bug_report`: encontra card similar via palavras‑chave + IA; atualiza card existente ou cria novo
   - `bug_fixed`: comenta com resolução e arquiva
   - `feature_request`: cria novo card

## Documentação
- **[Visão Geral](docs/overview.md)**
- **[Instalação](docs/installation.md)**
- **[Configuração](docs/configuration.md)**
- **[Uso](docs/usage.md)**
- **[Adapters de Mensageria](docs/adapters.md)**
- **[Análise de IA](docs/ai.md)**
- **[Integração com Trello](docs/trello.md)**
- **[HTTP & Webhooks](docs/http-webhooks.md)**
- **[Estendendo o Pacote](docs/extending.md)**
## Requisitos
- PHP 8.2+
- Laravel 12.x
- Fila configurada e worker em execução
- Chave e token do Trello
- App do WhatsApp Cloud API configurado
- Chave de API da OpenAI

## Contribuição
Issues e PRs são bem-vindos. Siga PSR-12 e rode testes/linters antes de enviar.

## Licença
MIT
