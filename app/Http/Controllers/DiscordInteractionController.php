<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Tournament;
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

        return match ($name) {
            'schema' => $this->reply($this->scheduleEmbed(now())),
            'klassement' => $this->reply($this->standingsEmbed()),
            'volgende' => $this->reply($this->nextEmbed()),
            'next' => $this->reply($this->nextGameEmbed()),
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
     * @param  array<string, mixed>  $embed
     */
    private function reply(array $embed): JsonResponse
    {
        $embed['color'] = self::GREEN;
        $embed['footer'] = ['text' => 'MBLAN26'];

        return response()->json([
            'type' => 4,
            'data' => ['embeds' => [$embed]],
        ]);
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
