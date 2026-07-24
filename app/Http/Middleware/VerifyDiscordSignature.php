<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies the Ed25519 signature Discord attaches to every interaction request.
 * Discord requires this: an endpoint that does not reject forged/unsigned
 * requests fails verification in the Developer Portal.
 */
class VerifyDiscordSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $publicKey = config('discord.public_key');
        $signature = $request->header('X-Signature-Ed25519');
        $timestamp = $request->header('X-Signature-Timestamp');

        if (empty($publicKey) || empty($signature) || empty($timestamp)) {
            abort(401, 'Missing signature.');
        }

        $valid = sodium_crypto_sign_verify_detached(
            sodium_hex2bin($signature),
            $timestamp.$request->getContent(),
            sodium_hex2bin($publicKey),
        );

        if (! $valid) {
            abort(401, 'Invalid signature.');
        }

        return $next($request);
    }
}
