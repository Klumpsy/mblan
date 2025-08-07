<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->get('api_key');
        $validApiKey = config('app.api_key');

        if (empty($validApiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API key authentication not configured'
            ], 500);
        }

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required. Provide it via X-API-Key header or api_key parameter.'
            ], 401);
        }

        if ($apiKey !== $validApiKey) {
            Log::warning('Invalid API key attempt', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'provided_key' => substr($apiKey, 0, 8) . '...',
                'endpoint' => $request->path()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid API key'
            ], 403);
        }

        return $next($request);
    }
}
