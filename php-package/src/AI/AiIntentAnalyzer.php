<?php

namespace Tonso\TrelloTracker\AI;

use Illuminate\Support\Facades\Log;
use Tonso\TrelloTracker\AI\Contracts\LLMClient;
use Tonso\TrelloTracker\AI\DTO\StructuredIntent;

final class AiIntentAnalyzer
{
    public function __construct(
        private readonly LLMClient $llm
    ) {}

    public function analyze(string $message): StructuredIntent
    {
        $json = $this->llm->analyzeIntent(
            systemPrompt: $this->systemPrompt(),
            userMessage: $message
        );

        $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        return StructuredIntent::fromArray($data);
    }
    public function analyzeBatch(string $context): array
    {
        $json = $this->llm->analyzeIntent(
            systemPrompt: $this->batchSystemPrompt(),
            userMessage: $context
        );

        $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

        // Ensure we return an array of objects
        return array_map(
            fn($item) => StructuredIntent::fromArray($item),
            $data['tasks'] ?? []
        );
    }

    public function findMatchInBatch(string $newIntent, array $candidates): array
    {
        $systemPrompt = <<<PROMPT
Você é um motor de DEDUPLICAÇÃO EXTREMAMENTE RIGOROSO.

OBJETIVO:
Determinar se duas tarefas representam O MESMO problema técnico.

PROCESSO:
1. Compare o type do novo problema com os cartões existentes.
2. Compare canonical.object.
3. Compare canonical.action.

REGRA DE MATCH:
Match = true SOMENTE se:
✔ Mesmo type
✔ Mesmo canonical.object
✔ Mesmo canonical.action

Ignore completamente diferenças de texto, adjetivos ou contexto.

REGRA DE SEGURANÇA:
Na dúvida, retorne match: false.

SAÍDA:
Retorne apenas um objeto json válido.

Formato:
{
  "match": boolean,
  "card_id": "string|null",
  "confidence": 0.0-1.0,
  "reason": "Explique a comparação canônica"
}
PROMPT;

        $userMessage = "NOVA INTENÇÃO:\n{$newIntent}\n\nLISTA DE CARTÕES EXISTENTES:\n" . json_encode($candidates);

        try {
            $json = $this->llm->analyzeIntent($systemPrompt, $userMessage);
            return json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            Log::error("Batch match failed: " . $e->getMessage());
            return ['match' => false];
        }
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
Você é um analisador técnico de ALTA PRECISÃO.

OBJETIVO:
Extrair UMA ÚNICA tarefa técnica claramente identificável a partir da mensagem do usuário.

PRINCÍPIO FUNDAMENTAL:
Cada tarefa representa UM problema técnico único.
Nunca misture problemas diferentes em uma mesma tarefa.

PROCESSO OBRIGATÓRIO (MENTAL):
1. Identifique o OBJETO PRINCIPAL afetado.
2. Identifique a FALHA ou AÇÃO ocorrida.
3. Ignore sintomas secundários ou consequências indiretas.

REGRA CRÍTICA DE IDENTIDADE:
Você DEVE extrair uma identidade canônica do problema.

A identidade canônica consiste em:
- object: o conceito técnico principal afetado
- action: a ação, falha ou intenção principal

Essa identidade deve ser estável mesmo se o texto mudar.

Exemplo:
"Implementar alerta sutil de falta de conexão"
"Implementar alerta de conexão perdida no app"

→ object: "conexao"
→ action: "alerta_ausencia"

REGRAS DE TÍTULO:
- Curto
- Técnico
- Canônico
- Sem narrativa ou contexto de usuário

❌ "Usuário não consegue bater ponto"
✅ "Falha na leitura do cartão"

IDIOMA:
Use Português do Brasil.

SAÍDA:
Responda exclusivamente em json válido.
Não escreva nenhum texto fora do json.

Formato json esperado:
{
  "type": "bug_report|feature_request|bug_fixed|unknown",
  "title": "Título técnico e canônico",
  "description": "Descrição objetiva do problema",
  "steps_to_reproduce": ["passo 1", "passo 2"],
  "tags": ["mobile", "backend", "ui"],
  "canonical": {
    "object": "string",
    "action": "string"
  },
  "resolution": null
}
PROMPT;
    }

    private function batchSystemPrompt(): string
    {
        return <<<PROMPT
Você é um Motor de Extração de Tarefas ULTRA CONSERVADOR.

REGRA SUPREMA:
Nunca misture problemas técnicos diferentes em uma mesma tarefa.

REGRAS DE ATOMICIDADE:
1. Cada tarefa representa exatamente 1 problema técnico.
2. Se o usuário mencionar dois problemas diferentes, gere DUAS tarefas.
3. Nunca una problemas apenas por parecerem relacionados.
4. Na dúvida, sempre SEPARE.

REGRA CRÍTICA:
Cada tarefa DEVE conter um campo "canonical" com:
- object
- action

Esses valores devem ser usados para deduplicação.
Textos diferentes podem compartilhar a mesma identidade canônica.

REGRAS DE CONTEXTO:
- Mensagens marcadas como [ALREADY SAVED] são apenas contexto.
- Nunca gere tarefas a partir delas.
- Mensagens [NEW] podem complementar um problema já descrito, se forem claramente o MESMO problema.

FILTRAGEM DE RUÍDO (OBRIGATÓRIA):
Ignore completamente:
- Pedidos para criar cards
- Reclamações sobre processo
- Conversa meta sobre Trello ou organização

SAÍDA:
Responda exclusivamente em json válido.
Não escreva texto fora do json.

Formato json esperado:
{
  "tasks": [
    {
      "type": "bug_report|feature_request|bug_fixed|unknown",
      "title": "Título técnico e canônico",
      "description": "Resumo técnico completo",
      "steps_to_reproduce": ["passo 1"],
      "tags": ["mobile", "ui"],
      "canonical": {
        "object": "string",
        "action": "string"
      },
      "resolution": "Apenas se bug_fixed"
    }
  ]
}

Se nenhum problema técnico REAL for encontrado, retorne:
{"tasks": []}
PROMPT;
    }
}
