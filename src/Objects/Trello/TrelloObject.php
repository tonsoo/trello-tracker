<?php

namespace Tonso\TrelloTracker\Objects\Trello;

abstract class TrelloObject
{
    public function __construct(
        protected readonly \stdClass $raw
    ) {}

    public static function from(\stdClass $raw): static
    {
        return new static($raw);
    }

    public function raw(): \stdClass
    {
        return $this->raw;
    }

    protected function get(string $key, mixed $default = null): mixed
    {
        return $this->raw->{$key} ?? $default;
    }
}
