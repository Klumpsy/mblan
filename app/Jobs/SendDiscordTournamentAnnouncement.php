<?php

namespace App\Jobs;

use App\Models\Tournament;
use App\Services\DiscordWebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDiscordTournamentAnnouncement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Tournament $tournament,
        private string $type = 'started'
    ) {}

    public function handle(DiscordWebhookService $discordService): void
    {
        try {
            $success = match ($this->type) {
                'started' => $discordService->announceTournament($this->tournament),
                'ended' => $discordService->announceTournamentEnd($this->tournament),
                'results' => $discordService->sendTournamentResults($this->tournament),
                default => false
            };

            if (!$success) {
                Log::warning("Discord announcement failed for tournament: {$this->tournament->name} (type: {$this->type})");
                $this->fail('Discord webhook request failed');
            }
        } catch (\Exception $e) {
            Log::error("Discord announcement error: {$e->getMessage()}", [
                'tournament' => $this->tournament->name,
                'type' => $this->type
            ]);

            $this->fail($e);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Discord announcement job failed permanently", [
            'tournament' => $this->tournament->name,
            'type' => $this->type,
            'error' => $exception->getMessage()
        ]);
    }
}
