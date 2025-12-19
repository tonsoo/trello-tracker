<?php

namespace Tonso\TrelloTracker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Tonso\TrelloTracker\Models\Transcript;

class TranscriptController extends Controller
{
    public function transcribe(Request $request, string $meetingId)
    {
        $data = $request->validate([
            'endedAt' => 'required',
            'transcript' => 'required|array'
        ]);

        $record = Transcript::firstOrNew(['meeting_id' => $meetingId]);

        $existingMessages = $record->body ?? [];

        $merged = collect($existingMessages)
            ->concat($data['transcript'])
            ->unique(function ($item) {
                $shortTime = substr($item['timestamp'], 0, 19);
                return $shortTime . $item['text'];
            })
            ->sortBy('timestamp')
            ->values()
            ->toArray();

        $record->status = 'active';
        $record->body = $merged;
        $record->ended_at = null;
        $record->save();

        return response()->json([
            'status' => 'merged',
            'count' => count($merged)
        ])
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}