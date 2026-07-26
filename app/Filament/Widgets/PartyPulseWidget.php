<?php

namespace App\Filament\Widgets;

use App\Models\Photo;
use App\Models\Signup;
use App\Models\Tournament;
use App\Models\User;
use App\Models\UserAchievement;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

/**
 * At-a-glance counters for the LAN: accounts, sign-ups, RSVP "komt", active
 * tournaments, timeline photos and unlocked achievements.
 */
class PartyPulseWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'MBLAN26 in cijfers';

    protected function getStats(): array
    {
        // Matches DiscordInteractionController::RSVP_CACHE_KEY.
        $rsvps = Cache::get('discord.rsvp', []);
        $coming = collect($rsvps)->where('status', 'yes')->count();

        return [
            Stat::make('Spelers', User::count())
                ->description('Geregistreerde accounts')
                ->color('success'),

            Stat::make('Aanmeldingen', Signup::count())
                ->description('Aanmeldingen voor de LAN'),

            Stat::make('Komt (Discord)', $coming)
                ->description('Bevestigd via /rsvp')
                ->color('success'),

            Stat::make('Actieve toernooien', Tournament::where('is_active', true)->count()),

            Stat::make("Foto's", Photo::count())
                ->description('Op de tijdlijn'),

            Stat::make('Prestaties', UserAchievement::count())
                ->description('Ontgrendeld'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
