<?php

namespace Tonso\TrelloTracker\Console\Commands;

use Illuminate\Console\Command;
use Tonso\TrelloTracker\Models\Transcript;
use Carbon\Carbon;

class MonitorIdleTranscripts extends Command
{
    /**
     * The name and signature of the console command.
     * {minutes?} makes the argument optional.
     */
    protected $signature = 'trello-tracker:monitor-idle {minutes=5}';

    protected $description = 'Check for transcripts that have been silent for X minutes';

    public function handle()
    {
        $minutes = (int) $this->argument('minutes');

        $this->info("Checking for transcripts silent for more than {$minutes} minutes...");

        $idleTranscripts = Transcript::where('status', 'active')
            ->where('updated_at', '<=', Carbon::now()->subMinutes($minutes))
            ->get();

        if ($idleTranscripts->isEmpty()) {
            $this->comment("No idle transcripts found.");
            return 0;
        }

        foreach ($idleTranscripts as $transcript) {
            $this->info("Meeting {$transcript->meeting_id} has been silent since {$transcript->updated_at}. Processing...");

            ProcessCompleteTranscript::dispatch($transcript);

            $transcript->update(['status' => 'processing']);
        }

        $this->info("Monitoring complete.");
        return 0;
    }
}