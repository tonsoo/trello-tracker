<?php

namespace Tonso\TrelloTracker\Services\Trello;

use Illuminate\Support\Str;
use Tonso\TrelloTracker\AI\AiIntentAnalyzer;
use Tonso\TrelloTracker\AI\DTO\StructuredIntent;
use Tonso\TrelloTracker\Objects\Trello\Card;

final class TrelloOrchestrator
{
    public function __construct(
        private readonly TrelloService $trello,
        private readonly AiIntentAnalyzer $ai,
    ) {}

    private function appendBug(Card $card, StructuredIntent $intent): void
    {
        $this->trello->addComment(
            $card->id(),
            "🐞 New bug report:\n{$intent->description}"
        );
    }

    private function appendFeatureContext(Card $card, StructuredIntent $intent): void
    {
        $this->trello->addComment(
            $card->id(),
            "✨ Additional context:\n{$intent->description}"
        );
    }

    private function closeBug(Card $card, StructuredIntent $intent): void
    {
        $this->trello->addComment(
            $card->id(),
            "✅ Fixed:\n{$intent->resolution}"
        );

        $this->trello->archiveCard($card->id());
    }

    private function updateExistingCard(Card $card, StructuredIntent $intent): void
    {
        match ($intent->type) {
            'bug_report'      => $this->appendBug($card, $intent),
            'feature_request' => $this->appendFeatureContext($card, $intent),
            'bug_fixed'       => $this->closeBug($card, $intent),
            default           => null,
        };
    }

    private function createNewCard(StructuredIntent $intent): void
    {
        $prefix = match ($intent->type) {
            'bug_report'      => '🐞 ',
            'feature_request' => '✨ ',
            default           => '📝 ',
        };

        $canonicalBlock = '';
        if (!empty($intent->canonical)) {
            $canonicalBlock = "\n\n<!-- canonical:" . json_encode($intent->canonical) . " -->";
        }

        $this->trello->createCard(
            name: $prefix.$intent->title,
            desc: ($intent->description ?? '') . $canonicalBlock
        );
    }

    public function handle(StructuredIntent $intent): void
    {
        $card = $this->findMatchingCard($intent);

        if ($card) {
            $this->updateExistingCard($card, $intent);
            return;
        }

        $this->createNewCard($intent);
    }

    private function extractCanonicalFromCard(Card $card): ?array
    {
        $desc = $card->description() ?? '';

        if (!preg_match('/<!-- canonical:(.*?) -->/s', $desc, $m)) {
            return null;
        }

        return json_decode(trim($m[1]), true);
    }

    private function canonicalEquals(
        StructuredIntent $intent,
        array $cardCanonical
    ): bool {
        return
            ($intent->canonical['object'] ?? null) === ($cardCanonical['object'] ?? null)
            && ($intent->canonical['action'] ?? null) === ($cardCanonical['action'] ?? null);
    }

    private function findMatchingCard(StructuredIntent $intent): ?Card
    {
        $cards = $this->trello->cards();

        foreach ($cards as $card) {
            $cardCanonical = $this->extractCanonicalFromCard($card);

            if ($cardCanonical && !empty($intent->canonical)) {
                if ($this->canonicalEquals($intent, $cardCanonical)) {
                    return $card;
                }
            }
        }

        $slimCards = $cards->map(fn($card) => [
            'id' => $card->id(),
            'title' => $card->name(),
            'summary' => Str::limit($card->description() ?? '', 500),
        ]);

        foreach ($slimCards->chunk(100) as $chunk) {
            $result = $this->ai->findMatchInBatch(
                newIntent: "{$intent->type} | {$intent->title} | {$intent->description}",
                candidates: $chunk->values()->toArray()
            );

            if (($result['match'] ?? false) &&
                ($result['confidence'] ?? 0) >= config('trello-tracker.ai.similarity_threshold')) {
                return $cards->firstWhere('id', $result['card_id']);
            }
        }

        return null;
    }
}
