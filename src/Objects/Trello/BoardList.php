<?php

namespace Tonso\TrelloTracker\Objects\Trello;

final class BoardList extends TrelloObject
{
    public function id(): string
    {
        return $this->get('id');
    }

    public function name(): string
    {
        return $this->get('name');
    }

    public function closed(): bool
    {
        return (bool) $this->get('closed', false);
    }
}
