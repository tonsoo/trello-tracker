<?php

namespace Tonso\TrelloTracker\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Tonso\TrelloTracker\Models\Transcript;

class ProcessCompleteTranscript implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Transcript $transcript) {}

    public function handle()
    {
        try {
            Log::info("Processing finalized transcript for: " . $this->transcript->meeting_id);

            $this->transcript->update(['status' => 'completed']);

//            ProcessContextBatchJob::dispatch();
        } catch (\Exception $e) {
            $this->transcript->update(['status' => 'active']);
            throw $e;
        }
    }
}