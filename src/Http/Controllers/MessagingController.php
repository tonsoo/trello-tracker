<?php

namespace Tonso\TrelloTracker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Tonso\TrelloTracker\Jobs\ProcessIncomingMessageJob;
use Tonso\TrelloTracker\Messaging\Adapters\WhatsAppAdapter;
use Tonso\TrelloTracker\UseCases\ProcessIncomingMessage;

class MessagingController extends Controller
{
    public function whatsappAuth(Request $request)
    {
        $verifyToken = config('trello-tracker.messaging.whatsapp.secret');

        if (
            $request->get('hub_mode') === 'subscribe' &&
            $request->get('hub_verify_token') === $verifyToken
        ) {
            return response(
                $request->get('hub_challenge'),
                200
            );
        }

        return response('Unauthorized', 403);
    }

    public function whatsapp(
        Request $request,
        WhatsAppAdapter $adapter
    ) {
        Log::info('Received whatsapp request');
        $messages = $adapter->parse($request->all());

        foreach ($messages as $message) {
            ProcessIncomingMessageJob::dispatch($message);
        }

        return response()->json(['ok' => true]);
    }
}