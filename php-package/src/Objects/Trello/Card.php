<?php

namespace Tonso\TrelloTracker\Objects\Trello;

final class Card extends TrelloObject
{
    public function id(): string
    {
        return $this->get('id');
    }

    public function name(): string
    {
        return $this->get('name');
    }

    public function description(): ?string
    {
        return $this->get('desc');
    }

    public function listId(): string
    {
        return $this->get('idList');
    }

    public function boardId(): string
    {
        return $this->get('idBoard');
    }

    public function closed(): bool
    {
        return (bool) $this->get('closed', false);
    }

    public function url(): ?string
    {
        return $this->get('url');
    }

    public function labels(): array
    {
        return $this->get('labels', []);
    }
}
