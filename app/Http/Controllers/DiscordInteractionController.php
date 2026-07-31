<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Tournament;
use App\Models\User;
use App\Support\BeerMessages;
use App\Support\DiscordCommands;
use App\Support\WineMessages;
use App\Support\ScheduleTimeline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Handles Discord interaction callbacks (slash commands and button presses).
 * The request signature is already verified by the VerifyDiscordSignature
 * middleware. Responses use the Discord interaction response format.
 *
 * Interaction types: 1 = PING, 2 = APPLICATION_COMMAND, 3 = MESSAGE_COMPONENT.
 * Response types: 1 = PONG, 4 = CHANNEL_MESSAGE_WITH_SOURCE.
 */
class DiscordInteractionController extends Controller
{
    private const GREEN = 0x65E59A;
    private const EPHEMERAL = 64;
    private const RSVP_CACHE_KEY = 'discord.rsvp';

    public function handle(Request $request): JsonResponse
    {
        $type = (int) $request->input('type');

        return match ($type) {
            1 => response()->json(['type' => 1]), // PONG
            2 => $this->handleCommand($request),
            3 => $this->handleComponent($request),
            default => response()->json(['type' => 1]),
        };
    }

    private function handleCommand(Request $request): JsonResponse
    {
        $name = $request->input('data.name');

        if (is_string($name) && $name !== '') {
            \App\Models\DiscordCommandLog::create([
                'command' => $name,
                'discord_user_id' => $request->input('member.user.id') ?? $request->input('user.id'),
            ]);
        }

        return match ($name) {
            'schema' => $this->reply($this->scheduleEmbed(now())),
            'klassement' => $this->reply($this->standingsEmbed()),
            'volgende' => $this->reply($this->nextEmbed()),
            'next' => $this->reply($this->nextGameEmbed()),
            'beer' => $this->handleBeer($request),
            'beercount' => $this->reply($this->beerCountEmbed()),
            'beerlist' => $this->reply($this->beerListEmbed()),
            'wine' => $this->handleWine($request),
            'winelist' => $this->reply($this->wineListEmbed()),
            'help' => $this->reply($this->helpEmbed(), true),
            default => $this->reply(['title' => 'Onbekend commando', 'description' => 'Dit commando ken ik niet.']),
        };
    }

    private function handleComponent(Request $request): JsonResponse
    {
        $customId = $request->input('data.custom_id', '');
        $user = $request->input('member.user') ?? $request->input('user');
        $userId = $user['id'] ?? null;
        $userName = $user['global_name'] ?? $user['username'] ?? 'Onbekend';

        if (! $userId || ! str_starts_with($customId, 'rsvp:')) {
            return $this->replyText('Onbekende actie.', true);
        }

        $status = $customId === 'rsvp:yes' ? 'yes' : 'no';

        $rsvps = Cache::get(self::RSVP_CACHE_KEY, []);
        $rsvps[$userId] = ['name' => $userName, 'status' => $status];
        Cache::put(self::RSVP_CACHE_KEY, $rsvps, now()->addDays(60));

        $going = count(array_filter($rsvps, fn ($r) => $r['status'] === 'yes'));
        $text = $status === 'yes'
            ? "Genoteerd, je komt naar MBLAN26. Tot dan. ({$going} aanmeldingen)"
            : 'Genoteerd, je komt niet. Jammer.';

        return $this->replyText($text, true);
    }

    /**
     * @return array<string, mixed>
     */
    private function scheduleEmbed(\Illuminate\Support\Carbon $date): array
    {
        $schedules = Schedule::with(['games', 'blocks'])
            ->whereDate('date', $date->toDateString())
            ->get();

        $lines = [];
        foreach ($schedules as $schedule) {
            foreach (ScheduleTimeline::forSchedule($schedule) as $item) {
                $time = $item->start ? $item->start->format('H:i') : 't.b.a.';
                $tag = $item->is_tournament ? ' (toernooi)' : '';
                $lines[] = "{$time} - {$item->name}{$tag}";
            }
        }

        return [
            'title' => 'Programma '.$date->translatedFormat('l d F'),
            'description' => $lines !== [] ? implode("\n", $lines) : 'Nog niets ingepland voor vandaag.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function standingsEmbed(): array
    {
        $tournaments = Tournament::with('game')->where('is_active', true)->get();

        if ($tournaments->isEmpty()) {
            return ['title' => 'Klassement', 'description' => 'Er zijn nu geen actieve toernooien.'];
        }

        $blocks = [];
        foreach ($tournaments as $tournament) {
            $rows = $tournament->getLeaderboard()->take(5);
            $ladder = '';
            foreach ($rows as $i => $row) {
                $ladder .= ($i + 1).'. '.$row['name'].' - '.$row['score'].' '.$tournament->scoreLabel()."\n";
            }
            $blocks[] = "**{$tournament->name}**\n".($ladder !== '' ? $ladder : 'Nog geen scores.');
        }

        return ['title' => 'Klassement', 'description' => implode("\n", $blocks)];
    }

    /**
     * @return array<string, mixed>
     */
    private function nextEmbed(): array
    {
        $now = now();
        $next = null;

        foreach (Schedule::with(['games', 'blocks'])->get() as $schedule) {
            foreach (ScheduleTimeline::forSchedule($schedule) as $item) {
                if ($item->start && $item->start->gt($now) && (! $next || $item->start->lt($next->start))) {
                    $next = $item;
                }
            }
        }

        if (! $next) {
            return ['title' => 'Volgende', 'description' => 'Er staat niets meer op het programma.'];
        }

        return [
            'title' => 'Volgende: '.$next->name,
            'description' => 'Begint om '.$next->start->format('H:i').' op '.$next->start->translatedFormat('l d F').'.',
        ];
    }

    /**
     * The next upcoming game (skips free-time blocks), with its image, title
     * and short description. Ordered by absolute start (day + time).
     *
     * @return array<string, mixed>
     */
    private function nextGameEmbed(): array
    {
        $now = now();
        $next = null;

        foreach (Schedule::with(['games', 'blocks'])->get() as $schedule) {
            foreach (ScheduleTimeline::forSchedule($schedule) as $item) {
                if ($item->type !== 'game' || ! $item->start) {
                    continue;
                }
                if ($item->start->gt($now) && (! $next || $item->start->lt($next->start))) {
                    $next = $item;
                }
            }
        }

        if (! $next) {
            return ['title' => 'Volgende game', 'description' => 'Er staat geen game meer op het programma.'];
        }

        $when = 'Begint om '.$next->start->format('H:i').' op '.$next->start->translatedFormat('l d F').'.';
        $description = $next->short_description
            ? $next->short_description."\n\n".$when
            : $when;

        $embed = [
            'title' => $next->is_tournament ? 'Toernooi: '.$next->name : $next->name,
            'description' => $description,
        ];

        if ($next->game_id) {
            $embed['url'] = route('games.show', $next->game_id);
        }
        if ($next->image) {
            $embed['image'] = ['url' => asset('storage/'.$next->image)];
        }

        return $embed;
    }

    /**
     * /beer - log one beer for the Discord user (matched to their website
     * account by discord_id) and reply with a funny line that escalates with
     * their personal total. Unlinked users get a friendly ephemeral nudge.
     */
    private function handleBeer(Request $request): JsonResponse
    {
        $discordId = $request->input('member.user.id') ?? $request->input('user.id');
        $user = $discordId ? User::where('discord_id', $discordId)->first() : null;

        if (! $user) {
            return $this->reply([
                'title' => 'Nog geen account gekoppeld',
                'description' => 'Koppel eerst je Discord aan je MBLAN26-account op de site, dan tel ik je biertjes mee.',
            ], true);
        }

        $count = $user->drinkBeer();

        // System-award any beer/Discord achievements this unlocks (notifies Discord).
        app(\App\Services\AchievementEvaluator::class)->sync($user);

        $name = $request->input('member.user.global_name')
            ?? $request->input('member.user.username')
            ?? $user->name;

        return $this->reply([
            'title' => 'Proost!',
            'description' => BeerMessages::line($name, $count),
        ]);
    }

    /**
     * /wine - log one glass of wine for the Discord user, the classier
     * counterpart of /beer. Same account matching, own counter.
     */
    private function handleWine(Request $request): JsonResponse
    {
        $discordId = $request->input('member.user.id') ?? $request->input('user.id');
        $user = $discordId ? User::where('discord_id', $discordId)->first() : null;

        if (! $user) {
            return $this->reply([
                'title' => 'Nog geen account gekoppeld',
                'description' => 'Koppel eerst je Discord aan je MBLAN26-account op de site, dan tel ik je wijntjes mee.',
            ], true);
        }

        $count = $user->drinkWine();

        $name = $request->input('member.user.global_name')
            ?? $request->input('member.user.username')
            ?? $user->name;

        return $this->reply([
            'title' => 'Santé!',
            'description' => WineMessages::line($name, $count),
        ]);
    }

    /**
     * /winelist - a ranked leaderboard of everyone who has logged a glass,
     * formatted like the beer list.
     *
     * @return array<string, mixed>
     */
    private function wineListEmbed(): array
    {
        $users = User::where('wine_count', '>', 0)
            ->orderByDesc('wine_count')
            ->orderBy('name')
            ->get(['name', 'wine_count']);

        if ($users->isEmpty()) {
            return ['title' => 'Wijnranglijst', 'description' => 'Nog geen wijn genoteerd. De kelder wacht op de eerste kenner.'];
        }

        $shown = $users->take(25);
        $rows = [
            str_pad('#', 3).str_pad('Deelnemer', 22).'Wijn',
            str_repeat('-', 29),
        ];
        foreach ($shown as $i => $user) {
            $name = mb_strimwidth($user->name, 0, 20, '..');
            $rows[] = str_pad((string) ($i + 1).'.', 3).str_pad($name, 22).$user->wine_count;
        }

        $description = "```\n".implode("\n", $rows)."\n```";
        if ($users->count() > $shown->count()) {
            $rest = $users->count() - $shown->count();
            $description .= "\n... en nog {$rest} andere deelnemers.";
        }

        return ['title' => 'Wijnranglijst', 'description' => $description];
    }

    /**
     * /beercount - the grand total of all beers drunk across every account.
     *
     * @return array<string, mixed>
     */
    private function beerCountEmbed(): array
    {
        $total = (int) User::sum('beer_count');
        $drinkers = User::where('beer_count', '>', 0)->count();

        if ($total === 0) {
            return ['title' => 'Bierteller', 'description' => 'Er is nog geen enkel biertje genoteerd. Wie opent de bar?'];
        }

        $noun = $total === 1 ? 'biertje' : 'biertjes';
        $who = $drinkers === 1 ? '1 deelnemer' : "{$drinkers} deelnemers";

        return [
            'title' => 'Bierteller',
            'description' => "In totaal zijn er **{$total}** {$noun} gedronken door {$who}. Proost op MBLAN26.",
        ];
    }

    /**
     * /beerlist - a ranked leaderboard of everyone who has logged a beer,
     * formatted as an aligned code block so it stays readable in Discord.
     *
     * @return array<string, mixed>
     */
    private function beerListEmbed(): array
    {
        $users = User::where('beer_count', '>', 0)
            ->orderByDesc('beer_count')
            ->orderBy('name')
            ->get(['name', 'beer_count']);

        if ($users->isEmpty()) {
            return ['title' => 'Bierranglijst', 'description' => 'Nog geen biertjes genoteerd. De ranglijst wacht op de eerste held.'];
        }

        $shown = $users->take(25);
        $rows = [
            str_pad('#', 3).str_pad('Deelnemer', 22).'Bier',
            str_repeat('-', 29),
        ];
        foreach ($shown as $i => $user) {
            $name = mb_strimwidth($user->name, 0, 20, '..');
            $rows[] = str_pad((string) ($i + 1).'.', 3).str_pad($name, 22).$user->beer_count;
        }

        $description = "```\n".implode("\n", $rows)."\n```";
        if ($users->count() > $shown->count()) {
            $rest = $users->count() - $shown->count();
            $description .= "\n... en nog {$rest} andere deelnemers.";
        }

        return ['title' => 'Bierranglijst', 'description' => $description];
    }

    /**
     * A listing of every slash command the bot offers, built from the shared
     * catalogue so it always matches what is registered with Discord.
     *
     * @return array<string, mixed>
     */
    private function helpEmbed(): array
    {
        $lines = [];
        foreach (DiscordCommands::all() as $command) {
            $lines[] = "`/{$command['name']}` - {$command['description']}";
        }

        return [
            'title' => 'Commando\'s',
            'description' => implode("\n", $lines),
        ];
    }

    /**
     * @param  array<string, mixed>  $embed
     */
    private function reply(array $embed, bool $ephemeral = false): JsonResponse
    {
        $embed['color'] = self::GREEN;
        $embed['footer'] = ['text' => 'MBLAN26'];

        $data = ['embeds' => [$embed]];
        if ($ephemeral) {
            $data['flags'] = self::EPHEMERAL;
        }

        return response()->json(['type' => 4, 'data' => $data]);
    }

    private function replyText(string $content, bool $ephemeral = false): JsonResponse
    {
        $data = ['content' => $content];
        if ($ephemeral) {
            $data['flags'] = self::EPHEMERAL;
        }

        return response()->json(['type' => 4, 'data' => $data]);
    }
}
