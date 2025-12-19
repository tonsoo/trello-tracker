<?php

namespace Tonso\TrelloTracker\Objects\Trello;

final class Board extends TrelloObject
{
    public function id(): string
    {
        return $this->get('id');
    }

    public function name(): string
    {
        return $this->get('name');
    }

    public function url(): ?string
    {
        return $this->get('url');
    }
}
