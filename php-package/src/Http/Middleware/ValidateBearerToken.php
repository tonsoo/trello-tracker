<?php

namespace Tonso\TrelloTracker\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateBearerToken
{
    public function handle(Request $request, \Closure $next): Response
    {
        $token = $request->bearerToken();

        $expectedToken = config('trello-tracker.transcriptions.secret_key');

        Log::info("token: $token, expected: $expectedToken");

        if (!$token || $token !== $expectedToken) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid or missing Bearer token.'
            ], 401)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        return $next($request);
    }
}