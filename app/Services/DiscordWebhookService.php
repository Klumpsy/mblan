<?php

namespace App\Services;

use App\Models\Game;
use App\Models\News;
use App\Models\Tournament;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Posts LAN-party updates to a Discord channel via an incoming webhook.
 *
 * House style: Dutch, sober, no decorative emoji. The only colour is the embed
 * side-bar, which uses the MBLAN green. When no webhook URL is configured the
 * service is a silent no-op, so local/test environments never make HTTP calls.
 */
class DiscordWebhookService
{
    /** MBLAN green, used for the embed side-bar. */
    private const COLOR = 0x65E59A;

    private ?string $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = config('discord.webhook_url');
    }

    /**
     * A tournament has opened for signups / gone active.
     */
    public function announceTournament(Tournament $tournament): bool
    {
        $fields = array_values(array_filter([
            $tournament->game?->name ? ['name' => 'Game', 'value' => $tournament->game->name, 'inline' => true] : null,
            $tournament->schedule?->name ? ['name' => 'Onderdeel', 'value' => $tournament->schedule->name, 'inline' => true] : null,
            $tournament->time_start ? ['name' => 'Aanvang', 'value' => (string) $tournament->time_start, 'inline' => true] : null,
            $tournament->description ? ['name' => 'Toelichting', 'value' => $tournament->description, 'inline' => false] : null,
        ]));

        return $this->sendEmbed(
            'Nieuw toernooi geopend',
            "{$tournament->name} staat klaar. Schrijf je in en pak de eerste plek.",
            $fields,
        );
    }

    /**
     * A tournament has been closed / concluded.
     */
    public function announceTournamentEnd(Tournament $tournament): bool
    {
        return $this->sendEmbed(
            'Toernooi afgelopen',
            "{$tournament->name} is afgerond. De eindstand volgt.",
            array_values(array_filter([
                $tournament->game?->name ? ['name' => 'Game', 'value' => $tournament->game->name, 'inline' => true] : null,
                ['name' => 'Deelnemers', 'value' => (string) $tournament->usersWithScores()->count(), 'inline' => true],
            ])),
        );
    }

    /**
     * The final ladder for a concluded tournament.
     */
    public function sendTournamentResults(Tournament $tournament): bool
    {
        $users = $tournament->usersWithScores()->withPivot('score', 'ranking')->orderBy('pivot_ranking')->get();
        $label = $tournament->scoreLabel();

        $ladder = '';
        foreach ($users->take(10) as $index => $user) {
            $pos = ($index + 1).'.';
            $ladder .= "{$pos} {$user->name} - {$user->pivot->score} {$label}\n";
        }

        return $this->sendEmbed(
            'Eindstand '.$tournament->name,
            $ladder !== '' ? $ladder : 'Geen deelnemers met een score.',
        );
    }

    /**
     * The top of a ladder changed during play: someone took first place.
     */
    public function announceLadderLeaderChange(Tournament $tournament, User $leader, int $score): bool
    {
        return $this->sendEmbed(
            'Nieuwe koploper',
            "{$leader->name} staat nu bovenaan bij {$tournament->name} met {$score} {$tournament->scoreLabel()}.",
        );
    }

    /**
     * Someone set a new best on the Arti Game leaderboard.
     */
    public function announceArtiRecord(User $user, int $catches, ?int $timeMs): bool
    {
        $time = $timeMs ? $this->formatTime($timeMs) : null;
        $body = "{$user->name} bereikte de schuur met {$catches}x gepakt"
            .($time ? " in {$time}" : '')
            .'. Wie doet het beter?';

        return $this->sendEmbed('Nieuw record in Het Arti Spel', $body);
    }

    /**
     * A scheduled item is about to start.
     */
    public function announceScheduleReminder(string $name, Carbon $start, ?string $scheduleName, bool $isTournament, ?string $url = null): bool
    {
        $minutes = max(0, now()->diffInMinutes($start, false));
        $when = $minutes <= 1 ? 'begint zo' : "begint over {$minutes} minuten";
        $lead = $isTournament ? 'Toernooi' : 'Onderdeel';

        $fields = array_values(array_filter([
            $scheduleName ? ['name' => 'Onderdeel', 'value' => $scheduleName, 'inline' => true] : null,
            ['name' => 'Aanvang', 'value' => $start->format('H:i'), 'inline' => true],
            $url ? ['name' => 'Meer', 'value' => $url, 'inline' => false] : null,
        ]));

        return $this->sendEmbed("{$lead}: {$name} {$when}", "Zet je klaar. {$name} {$when} om {$start->format('H:i')}.", $fields);
    }

    /**
     * The programme for a single day.
     *
     * @param  array<int, string>  $lines
     */
    public function sendDailyDigest(Carbon $date, array $lines): bool
    {
        $body = $lines !== [] ? implode("\n", $lines) : 'Nog niets ingepland voor vandaag.';

        return $this->sendEmbed(
            'Programma '.$date->translatedFormat('l d F'),
            $body,
        );
    }

    /**
     * A game reached a like milestone.
     */
    public function announceGameLikeMilestone(Game $game, int $count): bool
    {
        return $this->sendEmbed(
            'Game in de smaak',
            "{$game->name} heeft {$count} likes. Stem ook op je favorieten in het speelschema.",
        );
    }

    /**
     * A user unlocked an achievement.
     */
    public function sendAchievementNotification(User $user, $achievement): bool
    {
        return $this->sendEmbed(
            'Prestatie behaald',
            "{$user->name} verdiende: {$achievement->name}.",
            $achievement->description ? [['name' => 'Toelichting', 'value' => $achievement->description, 'inline' => false]] : [],
        );
    }

    /**
     * A news item was published.
     */
    public function announceNews(News $news): bool
    {
        $body = $news->preview_text
            ?: \Illuminate\Support\Str::limit(trim(strip_tags($news->content)), 300);

        $fields = $news->author
            ? [['name' => 'Door', 'value' => $news->author->name, 'inline' => true]]
            : [];

        return $this->sendEmbed(
            $news->title,
            $body !== '' ? $body : 'Nieuw bericht op de site.',
            $fields,
            $news->image ? asset('storage/'.$news->image) : null,
            route('news.show', $news),
            config('discord.news_webhook_url'),
        );
    }

    /**
     * The team line-up for a tournament: each team and its members.
     */
    public function announceTeams(Tournament $tournament): bool
    {
        $members = $tournament->usersWithScores()->get();

        $teams = $members
            ->filter(fn ($user) => ! is_null($user->pivot->team_number))
            ->groupBy(fn ($user) => $user->pivot->team_number)
            ->sortKeys();

        if ($teams->isEmpty()) {
            return $this->sendEmbed('Teams '.$tournament->name, 'Er zijn nog geen teams ingedeeld.');
        }

        $blocks = [];
        foreach ($teams as $number => $group) {
            $name = $group->first()->pivot->team_name ?: "Team {$number}";
            $blocks[] = "**{$name}**\n".$group->pluck('name')->implode(', ');
        }

        return $this->sendEmbed('Teams '.$tournament->name, implode("\n\n", $blocks));
    }

    /**
     * Build a single-embed payload and post it.
     *
     * @param  array<int, array{name: string, value: string, inline: bool}>  $fields
     */
    private function sendEmbed(string $title, string $description, array $fields = [], ?string $image = null, ?string $url = null, ?string $webhookUrl = null): bool
    {
        $embed = [
            'title' => $title,
            'description' => $description,
            'color' => self::COLOR,
            'footer' => ['text' => 'MBLAN26'],
            'timestamp' => now()->toISOString(),
        ];

        if ($fields !== []) {
            $embed['fields'] = $fields;
        }
        if ($url) {
            $embed['url'] = $url;
        }
        if ($image) {
            $embed['image'] = ['url' => $image];
        }

        return $this->sendWebhook(['embeds' => [$embed]], $webhookUrl);
    }

    private function formatTime(int $ms): string
    {
        $seconds = intdiv($ms, 1000);

        return intdiv($seconds, 60).':'.str_pad((string) ($seconds % 60), 2, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sendWebhook(array $payload, ?string $webhookUrl = null): bool
    {
        // Post to the given channel webhook, falling back to the default one.
        $url = $webhookUrl ?: $this->webhookUrl;

        // No webhook configured -> silently do nothing (local, testing, or not set up yet).
        if (empty($url)) {
            return false;
        }

        // Always post under a fixed display name, so messages read as one sender
        // regardless of how the underlying webhook happens to be named.
        if ($name = config('discord.webhook_name')) {
            $payload['username'] = $name;
        }

        try {
            // No automatic retry: a Discord webhook POST is not idempotent, so
            // retrying after a slow-but-successful post (read timeout) creates a
            // duplicate message. One attempt only; failures are logged.
            $response = Http::timeout((int) config('discord.webhook_timeout', 10))
                ->post($url, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::warning('Discord webhook failed', ['status' => $response->status()]);

            return false;
        } catch (\Throwable $e) {
            Log::warning('Discord webhook error: '.$e->getMessage());

            return false;
        }
    }
}
