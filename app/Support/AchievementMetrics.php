<?php

namespace App\Support;

use App\Models\Photo;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The live values automatic achievements can be measured against. Each metric
 * maps a key (stored on achievements.metric) to a label (shown in the admin
 * form) and a resolver that reads the current value for a user. Add a metric
 * here and it becomes selectable for new achievements — the evaluator needs no
 * changes. Resolvers are defensive: they never throw, returning 0 on trouble.
 */
class AchievementMetrics
{
    /**
     * @return array<string, array{label: string, resolve: callable(User): int}>
     */
    public static function definitions(): array
    {
        return [
            'beer' => [
                'label' => 'Biertjes gedronken',
                'resolve' => fn (User $u) => (int) ($u->beer_count ?? 0),
            ],
            'photos' => [
                'label' => "Foto's geplaatst (tijdlijn)",
                'resolve' => fn (User $u) => $u->photos()->count(),
            ],
            'likes_received' => [
                'label' => 'Reacties ontvangen op eigen foto\'s',
                'resolve' => fn (User $u) => Reaction::query()
                    ->where('reactions.reactable_type', Photo::class)
                    ->join('photos', 'photos.id', '=', 'reactions.reactable_id')
                    ->where('photos.user_id', $u->id)
                    ->count(),
            ],
            'reactions_given' => [
                'label' => 'Reacties gegeven',
                'resolve' => fn (User $u) => $u->reactionsGiven()->count(),
            ],
            'game_likes' => [
                'label' => 'Games geliket',
                'resolve' => fn (User $u) => $u->likedGames()->count(),
            ],
            'tournament_signups' => [
                'label' => 'Toernooi-aanmeldingen',
                'resolve' => fn (User $u) => $u->tournamentRegistrations()->count(),
            ],
            'barn_completed' => [
                'label' => 'Schuur bereikt in Het Arti Spel (ja/nee)',
                'resolve' => fn (User $u) => $u->barn_completed ? 1 : 0,
            ],
            'discord_linked' => [
                'label' => 'Discord gekoppeld (ja/nee)',
                'resolve' => fn (User $u) => $u->discord_id ? 1 : 0,
            ],

            // --- Aanmeldingen / edities ---
            'signups' => [
                'label' => 'Aanmeldingen (bevestigd)',
                'resolve' => fn (User $u) => $u->signups()->where('confirmed', true)->count(),
            ],
            'camping' => [
                'label' => 'Blijft slapen op de camping (ja/nee)',
                'resolve' => fn (User $u) => $u->signups()->where('confirmed', true)->where('stays_on_campsite', true)->exists() ? 1 : 0,
            ],
            'barbecue' => [
                'label' => 'Sluit aan bij de barbecue (ja/nee)',
                'resolve' => fn (User $u) => $u->signups()->where('confirmed', true)->where('joins_barbecue', true)->exists() ? 1 : 0,
            ],
            'tshirt' => [
                'label' => 'Bestelt een MBLAN-shirt (ja/nee)',
                'resolve' => fn (User $u) => $u->signups()->where('confirmed', true)->where('wants_tshirt', true)->exists() ? 1 : 0,
            ],

            // --- Toernooien ---
            'tournaments_played' => [
                'label' => 'Toernooien gespeeld (met score)',
                'resolve' => fn (User $u) => DB::table('tournament_user')->where('user_id', $u->id)->count(),
            ],
            'tournaments_won' => [
                'label' => 'Toernooien gewonnen (1e plek)',
                'resolve' => fn (User $u) => DB::table('tournament_user')->where('user_id', $u->id)->where('ranking', 1)->count(),
            ],
            'tournament_podiums' => [
                'label' => 'Podiumplekken in toernooien (top 3)',
                'resolve' => fn (User $u) => DB::table('tournament_user')->where('user_id', $u->id)->whereBetween('ranking', [1, 3])->count(),
            ],
        ];
    }

    /** Key => label, for the admin metric picker. */
    public static function options(): array
    {
        return array_map(fn ($d) => $d['label'], self::definitions());
    }

    public static function has(?string $key): bool
    {
        return $key !== null && array_key_exists($key, self::definitions());
    }

    /** Current value of a metric for a user; 0 if the metric is unknown or errors. */
    public static function value(User $user, ?string $key): int
    {
        if (! self::has($key)) {
            return 0;
        }

        try {
            return (int) self::definitions()[$key]['resolve']($user);
        } catch (\Throwable $e) {
            report($e);

            return 0;
        }
    }
}
