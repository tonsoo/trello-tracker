<?php

namespace Tonso\TrelloTracker\AI\DTO;

final class SimilarityResult
{
    public function __construct(
        public readonly bool $match,
        public readonly float $confidence,
        public readonly string $reason,
    ) {}
}
