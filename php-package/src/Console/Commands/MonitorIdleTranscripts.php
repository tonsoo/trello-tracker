<?php

namespace Tonso\TrelloTracker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Tonso\TrelloTracker\Jobs\ProcessCompleteTranscript;
use Tonso\TrelloTracker\Models\Transcript;
use Carbon\Carbon;

class MonitorIdleTranscripts extends Command
{
    /**
     * The name and signature of the console command.
     * {minutes?} makes the argument optional.
     */
    protected $signature = 'trello-tracker:monitor-idle {minutes=1}';

    protected $description = 'Check for transcripts that have been silent for X minutes';

    public function handle()
    {
        Log::info('Running transcript monitor');

        $minutes = (int) $this->argument('minutes');

        $this->info("Checking for transcripts silent for more than {$minutes} minutes...");

        $idleTranscripts = Transcript::where('status', 'active')
            ->where('updated_at', '<=', Carbon::now()->subMinutes($minutes))
            ->get();

        if ($idleTranscripts->isEmpty()) {
            $this->comment("No idle transcripts found.");
            Log::info('No trsncripts');
            return 0;
        }

        foreach ($idleTranscripts as $transcript) {
            $this->info("Meeting {$transcript->meeting_id} has been silent since {$transcript->updated_at}. Processing...");

            ProcessCompleteTranscript::dispatch($transcript);

            $transcript->update(['status' => 'processing']);
        }

        $this->info("Monitoring complete.");
        Log::info('Finished transcripts monitor');
        return 0;
    }
}