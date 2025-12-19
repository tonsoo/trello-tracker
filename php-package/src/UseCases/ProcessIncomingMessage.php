<?php

namespace Tonso\TrelloTracker\UseCases;

use Tonso\TrelloTracker\AI\AiIntentAnalyzer;
use Tonso\TrelloTracker\Services\Trello\TrelloOrchestrator;

final class ProcessIncomingMessage
{
    public function __construct(
        private readonly AiIntentAnalyzer $ai,
        private readonly TrelloOrchestrator $orchestrator,
    ) {}

    public function handle(string $message): void
    {
        $intent = $this->ai->analyze($message);

        $this->orchestrator->handle($intent);
    }
}
