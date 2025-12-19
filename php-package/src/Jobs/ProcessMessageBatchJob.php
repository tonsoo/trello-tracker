<?php

namespace Tonso\TrelloTracker\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tonso\TrelloTracker\UseCases\ProcessMessageBatch;

final class ProcessMessageBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(ProcessMessageBatch $useCase): void
    {
        $useCase->handle();
    }
}