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

        // Match on the Discord id first, then fall back to a matching email so
        // an existing account gets linked instead of duplicated.
        $user = User::where('discord_id', $discordUser->getId())->first();

        if (! $user && $discordUser->getEmail()) {
            $user = User::where('email', $discordUser->getEmail())->first();
        }

        if ($user) {
            $user->forceFill([
                'discord_id' => $discordUser->getId(),
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            // email_verified_at is not mass-assignable, so build then force-fill.
            $user = (new User())->forceFill([
                'name' => $discordUser->getNickname() ?: $discordUser->getName() ?: 'Speler',
                'email' => $discordUser->getEmail() ?: $discordUser->getId().'@discord.local',
                'password' => bcrypt(Str::random(40)),
                'discord_id' => $discordUser->getId(),
                'email_verified_at' => now(),
            ]);
            $user->save();
        }

        Auth::login($user, remember: true);

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
