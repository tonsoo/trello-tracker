<?php

namespace Tonso\TrelloTracker\AI;

use Tonso\TrelloTracker\AI\Contracts\LLMClient;
use Tonso\TrelloTracker\AI\DTO\SimilarityResult;
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

    public function compareIssue(
        string $newMessage,
        array $existingCard
    ): SimilarityResult {
        $json = $this->llm->analyzeIntent(
            systemPrompt: $this->similarityPrompt($existingCard),
            userMessage: $newMessage
        );

        $data = json_decode($json, true);

        return new SimilarityResult(
            match: (bool) ($data['match'] ?? false),
            confidence: (float) ($data['confidence'] ?? 0),
            reason: (string) ($data['reason'] ?? '')
        );
    }

    private function similarityPrompt(array $existingCard): string
    {
        return <<<PROMPT
You are an AI that determines whether two bug reports describe the SAME underlying issue.

Existing card:
Title: "{$existingCard['title']}"
Description: "{$existingCard['description']}"

New report:
The user message will be provided separately.

Return JSON only.

JSON schema:
{
  "match": boolean,
  "confidence": number,
  "reason": string
}

Guidelines:
- Consider the same issue even if symptoms differ (e.g. color change vs blank screen)
- Focus on underlying cause
- If uncertain, set match=false
PROMPT;
    }

    private function systemPrompt(): string
    {
        return <<<PROMPT
You are an AI that extracts structured task intents from messages.

You MUST return valid JSON only.

Supported intent types:
- bug_report
- feature_request
- bug_fixed
- status_update
- unknown

Rules:
- Be concise
- Never invent data
- If unsure, use type "unknown"
- Steps must be an array
- Tags must be lowercase

JSON schema:
{
  "type": string,
  "title": string,
  "description": string|null,
  "steps_to_reproduce": string[],
  "tags": string[],
  "resolution": string|null
}
PROMPT;
    }
}
