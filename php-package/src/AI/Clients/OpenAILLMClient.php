<?php

namespace Tonso\TrelloTracker\AI\Clients;

use OpenAI;
use Tonso\TrelloTracker\AI\Contracts\LLMClient;

final class OpenAILLMClient implements LLMClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'gpt-4.1-mini'
    ) {}

    public function analyzeIntent(string $systemPrompt, string $userMessage): string
    {
        $client = OpenAI::client($this->apiKey);

        $response = $client->chat()->create([
            'model' => $this->model,
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt,
                ],
                [
                    'role' => 'user',
                    'content' => $userMessage,
                ],
            ],
            'temperature' => 0.1,
        ]);

        return $response->choices[0]->message->content;
    }
}
