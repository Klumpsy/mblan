<?php

namespace App\Services;

use App\Models\Tournament;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordWebhookService
{
    private string $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = config('discord.webhook_url');
    }

    public function announceTournament($tournament): bool
    {
        $embed = [
            'title' => '🏆 Tournament Now Active!',
            'description' => "**{$tournament->name}** has started!",
            'color' => 0x00ff00, // Green color
            'fields' => [
                [
                    'name' => '🎮 Game',
                    'value' => $tournament->game->name,
                    'inline' => true
                ],
                [
                    'name' => '⏰ Start Time',
                    'value' => $tournament->time_start,
                    'inline' => true
                ],
                [
                    'name' => '⏱️ End Time',
                    'value' => $tournament->time_end,
                    'inline' => true
                ],
                [
                    'name' => '📅 Schedule',
                    'value' => $tournament->schedule->name,
                    'inline' => false
                ]
            ],
            'footer' => [
                'text' => 'Good luck to all participants!'
            ],
            'timestamp' => now()->toISOString()
        ];

        // Add description if available
        if ($tournament->description) {
            $embed['fields'][] = [
                'name' => '📝 Description',
                'value' => $tournament->description,
                'inline' => false
            ];
        }

        $payload = [
            'content' => '@everyone A new tournament is starting! 🎉',
            'embeds' => [$embed]
        ];

        return $this->sendWebhook($payload);
    }

    public function announceTournamentEnd(Tournament $tournament): bool
    {
        $embed = [
            'title' => '🏁 Tournament Ended',
            'description' => "**{$tournament->name}** has concluded!",
            'color' => 0xff9900, // Orange color
            'fields' => [
                [
                    'name' => '🎮 Game',
                    'value' => $tournament->game->name,
                    'inline' => true
                ],
                [
                    'name' => '👥 Participants',
                    'value' => $tournament->usersWithScores()->count(),
                    'inline' => true
                ],
                [
                    'name' => '⏱️ Duration',
                    'value' => $tournament->time_start . ' - ' . $tournament->time_end,
                    'inline' => true
                ]
            ],
            'footer' => [
                'text' => 'Thanks for participating!'
            ],
            'timestamp' => now()->toISOString()
        ];

        $payload = [
            'content' => '🎉 Tournament has ended!',
            'embeds' => [$embed]
        ];

        return $this->sendWebhook($payload);
    }


    public function sendTournamentResults($tournament): bool
    {
        $users = $tournament->usersWithScores()->orderBy('ranking')->get();

        $leaderboard = '';
        foreach ($users->take(10) as $index => $user) {
            $medal = match ($index) {
                0 => '🥇',
                1 => '🥈',
                2 => '🥉',
                default => ($index + 1) . '.'
            };

            $leaderboard .= "{$medal} {$user->name} - {$user->pivot->score} points\n";
        }

        $embed = [
            'title' => '🏆 Tournament Results',
            'description' => "Final results for **{$tournament->name}**",
            'color' => 0xffd700,
            'fields' => [
                [
                    'name' => '🎮 Game',
                    'value' => $tournament->game->name,
                    'inline' => true
                ],
                [
                    'name' => '👥 Total Participants',
                    'value' => $users->count(),
                    'inline' => true
                ],
                [
                    'name' => '📊 Final Leaderboard',
                    'value' => $leaderboard ?: 'No results available',
                    'inline' => false
                ]
            ],
            'footer' => [
                'text' => 'Congratulations to all participants!'
            ],
            'timestamp' => now()->toISOString()
        ];

        $payload = [
            'content' => '🎉 Tournament results are in!',
            'embeds' => [$embed]
        ];

        return $this->sendWebhook($payload);
    }

    public function sendCustomAnnouncement($title, $message, $color = 0x5865f2, $pingEveryone = false): bool
    {
        $embed = [
            'title' => $title,
            'description' => $message,
            'color' => $color,
            'footer' => [
                'text' => 'Tournament System Announcement'
            ],
            'timestamp' => now()->toISOString()
        ];

        $payload = [
            'embeds' => [$embed]
        ];

        if ($pingEveryone) {
            $payload['content'] = '@everyone';
        }

        return $this->sendWebhook($payload);
    }

    public function announceSchedule($schedule): bool
    {
        $tournaments = $schedule->tournaments()->with('game')->get();

        $tournamentList = '';
        foreach ($tournaments as $tournament) {
            $status = $tournament->is_active ? '🟢 Active' : '⚪ Scheduled';
            $tournamentList .= "**{$tournament->name}** ({$tournament->game->name}) - {$status}\n";
            $tournamentList .= "⏰ {$tournament->time_start->format('H:i')} - {$tournament->time_end->format('H:i')}\n\n";
        }

        $embed = [
            'title' => '📅 Schedule Active',
            'description' => "**{$schedule->name}** is now live!",
            'color' => 0x0099ff, // Blue color
            'fields' => [
                [
                    'name' => '🎮 Tournaments Today',
                    'value' => $tournamentList ?: 'No tournaments scheduled',
                    'inline' => false
                ],
                [
                    'name' => '📊 Edition',
                    'value' => $schedule->edition->year ?? 'N/A',
                    'inline' => true
                ],
                [
                    'name' => '🗓️ Date',
                    'value' => now()->format('d/m/Y'),
                    'inline' => true
                ]
            ],
            'footer' => [
                'text' => 'Check the schedule for more details!'
            ],
            'timestamp' => now()->toISOString()
        ];

        $payload = [
            'content' => '@everyone Today\'s gaming schedule is live! 🎮',
            'embeds' => [$embed]
        ];

        return $this->sendWebhook($payload);
    }

    private function sendWebhook($payload): bool
    {
        if (empty($this->webhookUrl)) {
            Log::error('Discord webhook URL not configured');
            return false;
        }

        try {
            $response = Http::timeout(10)->post($this->webhookUrl, $payload);

            if ($response->successful()) {
                Log::info('Discord webhook sent successfully');
                return true;
            }

            Log::error('Discord webhook failed', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('Discord webhook error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendAchievementNotification($user, $achievement): bool
    {
        $embed = [
            'title' => '🏆 Achievement Unlocked!',
            'description' => "**{$user->name}** has earned a new achievement!",
            'color' => 0xffd700,
            'fields' => [
                [
                    'name' => '🎖️ Achievement',
                    'value' => $achievement->name,
                    'inline' => false
                ]
            ],
            'footer' => [
                'text' => 'Keep up the great work!'
            ],
            'timestamp' => now()->toISOString()
        ];

        if (!empty($achievement->description)) {
            $embed['fields'][] = [
                'name' => '📝 Description',
                'value' => $achievement->description,
                'inline' => false
            ];
        }

        $payload = [
            'content' => "🎉 Congratulations **{$user->name}**!",
            'embeds' => [$embed]
        ];

        return $this->sendWebhook($payload);
    }
}
