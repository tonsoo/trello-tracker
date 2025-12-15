<?php

namespace Tonso\TrelloTracker\Objects\Trello;

final class Action extends TrelloObject
{
    public function id(): string
    {
        return (string) $this->get('id');
    }

    public function type(): string
    {
        return (string) $this->get('type');
    }

    /**
     * For comment actions, the text is inside data.text
     */
    public function text(): ?string
    {
        return $this->get('data')?->text ?? null;
    }

    public function date(): ?string
    {
        return $this->get('date');
    }

    public function memberCreator(): ?\stdClass
    {
        return $this->get('memberCreator');
    }

    public function cardId(): ?string
    {
        return $this->get('data')?->card?->id ?? null;
    }
}
