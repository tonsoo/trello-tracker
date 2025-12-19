<?php

namespace Tonso\TrelloTracker\Messaging\Contracts;

use Tonso\TrelloTracker\Messaging\IncomingMessage;

interface MessagingAdapter
{
    /**
     * @return IncomingMessage[]
     */
    public function parse(array $payload): array;
}
