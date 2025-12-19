<?php

namespace Tonso\TrelloTracker\AI\DTO;

final class StructuredIntent
{
    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $description,
        public readonly array $steps,
        public readonly array $tags,
        public readonly ?string $resolution,
        public readonly array $canonical,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            title: $data['title'] ?? $data['related_title'],
            description: $data['description'] ?? null,
            steps: $data['steps_to_reproduce'] ?? [],
            tags: $data['tags'] ?? [],
            resolution: $data['resolution'] ?? null,
            canonical: $data['canonical'] ?? [],
        );
    }
}
