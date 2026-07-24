<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class DiscordController extends Controller
{
    /**
     * Send the visitor to Discord to authorise.
     */
    public function redirect()
    {
        return Socialite::driver('discord')
            ->redirectUrl($this->callbackUrl())
            ->scopes(['identify', 'email'])
            ->redirect();
    }

    /**
     * The absolute callback URL, matching the one registered in the Discord app.
     * Falls back to the named route (based on APP_URL) when no override is set.
     */
    private function callbackUrl(): string
    {
        return config('services.discord.redirect') ?: route('discord.callback');
    }

    /**
     * Handle the callback from Discord: link or create the account, then log in.
     */
    public function callback()
    {
        try {
            $discordUser = Socialite::driver('discord')
                ->redirectUrl($this->callbackUrl())
                ->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Inloggen via Discord is mislukt. Probeer het opnieuw.',
            ]);
        }

        $discordId = $discordUser->getId();
        $email = $discordUser->getEmail();
        // Discord tells us whether the account's e-mail is verified. We only ever
        // trust the e-mail when it is, otherwise an attacker could register a
        // Discord account on someone else's e-mail and hijack their site account.
        $emailVerified = (bool) ($discordUser->user['verified'] ?? false);

        // 1. Strong match: an account already linked to this exact Discord id.
        $user = User::where('discord_id', $discordId)->first();

        // 2. Link an existing account by e-mail ONLY when Discord verified it,
        //    and never take over an account already tied to another Discord.
        if (! $user && $email && $emailVerified) {
            $candidate = User::where('email', $email)->first();

            if ($candidate && $candidate->discord_id && $candidate->discord_id !== $discordId) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Dit account is al aan een andere Discord gekoppeld.',
                ]);
            }

            $user = $candidate;
        }

        if ($user) {
            $user->forceFill([
                'discord_id' => $discordId,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            // New account. Only store the Discord e-mail when it is verified;
            // otherwise use a non-colliding placeholder so we can never occupy
            // (and later hand over) an e-mail the visitor has not proven they own.
            $safeEmail = ($email && $emailVerified) ? $email : $discordId.'@discord.local';

            $user = (new User())->forceFill([
                'name' => $discordUser->getNickname() ?: $discordUser->getName() ?: 'Speler',
                'email' => $safeEmail,
                'password' => bcrypt(Str::random(40)),
                'discord_id' => $discordId,
                'email_verified_at' => now(),
            ]);
            $user->save();
        }

        Auth::login($user, remember: true);

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
