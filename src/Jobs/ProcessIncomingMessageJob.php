<?php

namespace Tonso\TrelloTracker\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tonso\TrelloTracker\Messaging\IncomingMessage;
use Tonso\TrelloTracker\UseCases\ProcessIncomingMessage;

final class ProcessIncomingMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of attempts before failing.
     */
    public int $tries = 3;

    /**
     * Seconds before retrying.
     */
    public int $backoff = 10;

    public function __construct(
        public readonly IncomingMessage $message
    ) {}

    public function handle(ProcessIncomingMessage $useCase): void
    {
        if ($this->message->messageId) {
            $lock = cache()->lock(
                'incoming-message:'.$this->message->messageId,
                60
            );

            if (! $lock->get()) {
                return;
            }
        }

        $useCase->handle($this->message->text);
    }
}
