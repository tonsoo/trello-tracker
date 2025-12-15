<?php

namespace Tonso\TrelloTracker\Services\Trello;

use Tonso\TrelloTracker\AI\AiIntentAnalyzer;
use Tonso\TrelloTracker\AI\DTO\StructuredIntent;
use Tonso\TrelloTracker\Objects\Trello\Card;

final class TrelloOrchestrator
{
    public function __construct(
        private readonly TrelloService $trello,
        private readonly AiIntentAnalyzer $ai,
    ) {}

    public function handle(StructuredIntent $intent): void
    {
        match ($intent->type) {
            'bug_report'     => $this->handleBug($intent),
            'feature_request'=> $this->handleFeature($intent),
            'bug_fixed'      => $this->handleBugFixed($intent),
            default          => null,
        };
    }

    private function handleBug(StructuredIntent $intent): void
    {
        $card = $this->findSimilarCard($intent);

        if ($card) {
            $this->appendBugContext($card, $intent);
            return;
        }

        $this->createNewBugCard($intent);
    }

    private function appendBugContext(Card $card, StructuredIntent $intent): void
    {
        $current = $card->description() ?? '';

        $updatedDescription = trim($current)."\n\n"
            ."### New report\n"
            ."- {$intent->description}";

        $this->trello->updateDescription($card->id(), $updatedDescription);

        $this->trello->addComment(
            $card->id(),
            "New report added:\n{$intent->description}"
        );
    }

    private function createNewBugCard(StructuredIntent $intent): void
    {
        $this->trello->createCard(
            name: '🐞 '.$intent->title,
            desc: $this->formatBugDescription($intent)
        );
    }

    private function handleBugFixed(StructuredIntent $intent): void
    {
        $card = $this->findSimilarCard($intent);

        if (!$card) {
            return;
        }

        $this->trello->addComment(
            $card->id(),
            "✅ Fixed:\n{$intent->resolution}"
        );

        $this->trello->archiveCard($card->id());
    }

    private function handleFeature(StructuredIntent $intent): void
    {
        $this->trello->createCard(
            name: "✨ ".$intent->title,
            desc: $intent->description
        );
    }

    private function extractKeywords(StructuredIntent $intent): array
    {
        return array_values(array_unique(array_filter([
            $intent->title,
            ...$intent->tags,
            ...$intent->steps,
        ])));
    }

    private function scoreCard(Card $card, array $keywords): int
    {
        $haystack = strtolower(
            $card->name().' '.($card->description() ?? '')
        );

        $score = 0;

        foreach ($keywords as $keyword) {
            $keyword = strtolower($keyword);

            if (strlen($keyword) < 3) {
                continue;
            }

            if (str_contains($haystack, $keyword)) {
                $score++;
            }
        }

        return $score;
    }

    private function getCandidateCards(array $keywords, int $limit): \Illuminate\Support\Collection
    {
        return $this->trello->cards()
            ->map(fn (Card $card) => [
                'card' => $card,
                'score' => $this->scoreCard($card, $keywords),
            ])
            ->filter(fn ($item) => $item['score'] > 0)
            ->sortByDesc('score')
            ->take($limit)
            ->pluck('card');
    }

    private function findSimilarCard(StructuredIntent $intent): ?Card
    {
        $keywords = $this->extractKeywords($intent);

        $candidates = $this->getCandidateCards(
            keywords: $keywords,
            limit: config('trello-tracker.ai.max_similarity_candidates', 5)
        );

        $bestMatch = null;
        $bestConfidence = 0.0;

        foreach ($candidates as $card) {
            $result = $this->ai->compareIssue(
                newMessage: $intent->description ?? $intent->title,
                existingCard: [
                    'title' => $card->name(),
                    'description' => $card->description(),
                ]
            );

            if (
                $result->match &&
                $result->confidence > $bestConfidence
            ) {
                $bestMatch = $card;
                $bestConfidence = $result->confidence;
            }
        }

        return $bestConfidence >= config('trello-tracker.ai.similarity_threshold')
            ? $bestMatch
            : null;
    }

    private function formatBugDescription(StructuredIntent $intent): string
    {
        return implode("\n", array_filter([
            "## Description",
            $intent->description,
            "",
            "## Steps to reproduce",
            collect($intent->steps)
                ->map(fn ($s, $i) => ($i + 1).". {$s}")
                ->join("\n"),
        ]));
    }
}
