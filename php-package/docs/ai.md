# Análise de Intenção por IA

- Analyzer: `Tonso\TrelloTracker\AI\AiIntentAnalyzer`
- Contrato do cliente: `Tonso\TrelloTracker\AI\Contracts\LLMClient`
- Cliente padrão: `Tonso\TrelloTracker\AI\Clients\OpenAILLMClient`

## Extração de intenção
`AiIntentAnalyzer::analyze($message)` monta um prompt estruturado e espera JSON como retorno. Devolve `StructuredIntent` com:
- `type`: `bug_report | feature_request | bug_fixed | status_update | unknown`
- `title`
- `description` (pode ser nulo)
- `steps_to_reproduce` (array)
- `tags` (array)
- `resolution` (pode ser nulo)

## Checagem de similaridade
`AiIntentAnalyzer::compareIssue($newMessage, $existingCard)` retorna `SimilarityResult { match, confidence, reason }`, usado pelo `TrelloOrchestrator` para decidir se atualiza um card existente.

## Configuração
- Modelo e chave via `ai.openai.*`
- Limiares: `ai.similarity_threshold`, `ai.max_similarity_candidates`
