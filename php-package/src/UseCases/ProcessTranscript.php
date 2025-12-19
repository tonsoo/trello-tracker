<?php

namespace Tonso\TrelloTracker\UseCases;

use Tonso\TrelloTracker\AI\AiIntentAnalyzer;
use Tonso\TrelloTracker\Models\Transcript;
use Tonso\TrelloTracker\Services\Trello\TrelloOrchestrator;

final class ProcessTranscript
{
    public function __construct(
        private readonly AiIntentAnalyzer $ai,
        private readonly TrelloOrchestrator $orchestrator,
    ) {}

    public function handle(Transcript $transcript): void
    {
        $context = collect($transcript->body)
            ->map(function ($item) {
                $text = $item['text'] ?? '';
                return "[NEW]: " . $text;
            })
//            ->filter(fn($line) => strlen($line) > 10)
            ->join("\n");

        $intents = $this->ai->analyzeBatch($context);

        foreach ($intents as $intent) {
            $this->orchestrator->handle($intent);
        }
    }
}