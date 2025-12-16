# Estendendo o Pacote

## Substituir o cliente LLM
Faça o bind da sua implementação de `LLMClient`.

```php
use Tonso\TrelloTracker\AI\Contracts\LLMClient;

$this->app->singleton(LLMClient::class, fn () => new MyLLMClient(...));
```

## Adicionar um novo adapter de mensageria
Implemente `MessagingAdapter` e converta o payload da sua plataforma em DTOs `IncomingMessage`. Faça o bind onde você processa o webhook.

## Customizar a orquestração
Estenda ou decore `TrelloOrchestrator` para alterar as regras de roteamento de `bug_report`, `feature_request` ou `bug_fixed`.

## Criar fluxos Trello personalizados
Use os helpers do `TrelloService` para manipular cards, comentários, labels e campos personalizados.
