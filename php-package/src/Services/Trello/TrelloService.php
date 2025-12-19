<?php

namespace Tonso\TrelloTracker\Services\Trello;

use Illuminate\Support\Collection;
use Stevenmaguire\Services\Trello\Client as TrelloClient;
use Tonso\TrelloTracker\Objects\Trello\Action;
use Tonso\TrelloTracker\Objects\Trello\Board;
use Tonso\TrelloTracker\Objects\Trello\BoardList;
use Tonso\TrelloTracker\Objects\Trello\Card;

class TrelloService
{
    public function __construct(
        private readonly TrelloClient $client,
        private readonly string $boardId,
        private readonly string $defaultListId,
    ) {}

    public function board(): Board
    {
        return Board::from(
            $this->client->getBoard($this->boardId)
        );
    }

    public function lists(): Collection
    {
        return collect(
            $this->client->getBoardLists($this->boardId)
        )->map(fn ($list) => BoardList::from($list));
    }

    /**
     * @return Collection<Card>
     */
    public function cards(): Collection
    {
        return collect(
            $this->client->getBoardCards($this->boardId)
        )->map(fn ($card) => Card::from($card));
    }

    /* --------------------
     | Cards (core)
     |-------------------- */

    public function getCard(string $cardId): Card
    {
        return Card::from(
            $this->client->getCard($cardId)
        );
    }

    public function createCard(
        string $name,
        ?string $desc = null,
        ?string $listId = null,
        array $extra = []
    ): Card {
        return Card::from(
            $this->client->addCard(array_merge([
                'name'   => $name,
                'desc'   => $desc,
                'idList' => $listId ?? $this->defaultListId,
            ], $extra))
        );
    }

    public function updateCard(string $cardId, array $attributes): array
    {
        return $this->client->updateCard($cardId, $attributes);
    }

    public function deleteCard(string $cardId): void
    {
        $this->client->deleteCard($cardId);
    }

    /* --------------------
     | Card helpers
     |-------------------- */

    public function moveCard(string $cardId, string $listId): Card
    {
        return Card::from(
            $this->client->updateCardIdList($cardId, [
                'value' => $listId,
            ])
        );
    }

    public function renameCard(string $cardId, string $name): Card
    {
        return Card::from(
            $this->client->updateCardName($cardId, [
                'value' => $name,
            ])
        );
    }

    public function updateDescription(string $cardId, string $desc): Card
    {
        return Card::from(
            $this->client->updateCardDesc($cardId, [
                'value' => $desc,
            ])
        );
    }

    public function archiveCard(string $cardId): Card
    {
        return Card::from(
            $this->client->updateCardClosed($cardId, [
                'value' => true,
            ])
        );
    }

    /* --------------------
     | Comments (WhatsApp messages!)
     |-------------------- */

    public function addComment(string $cardId, string $message): Action
    {
        return Action::from(
            $this->client->addCardActionComment($cardId, [
                'text' => $message,
            ])
        );
    }

    /* --------------------
     | Labels
     |-------------------- */

    public function addLabel(string $cardId, string $labelId): Card
    {
        return Card::from(
            $this->client->addCardIdLabel($cardId, [
                'value' => $labelId,
            ])
        );
    }

    public function removeLabel(string $cardId, string $labelId): void
    {
        $this->client->deleteCardIdLabel($cardId, $labelId);
    }

    /* --------------------
     | Custom Fields
     |-------------------- */

    public function setCustomField(
        string $cardId,
        string $customFieldId,
        array $value
    ): Card {
        return Card::from(
            $this->client->updateCardCustomField(
                $cardId,
                $customFieldId,
                ['value' => $value]
            )
        );
    }
}