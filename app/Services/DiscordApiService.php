<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Talks to the Discord REST API with a bot token. Powers the integrations that
 * a plain incoming webhook cannot do: Guild Scheduled Events, granting a member
 * role on login, posting interactive (button) messages, and registering the
 * guild slash commands.
 *
 * Everything guards on configuration: when the bot token or guild id is missing
 * the service is a silent no-op, so the app is safe to run before any of this is
 * set up in the Discord Developer Portal.
 */
class DiscordApiService
{
    private const BASE = 'https://discord.com/api/v10';

    private ?string $token;
    private ?string $guildId;
    private ?string $applicationId;

    public function __construct()
    {
        $this->token = config('discord.bot_token');
        $this->guildId = config('discord.guild_id');
        $this->applicationId = config('discord.application_id');
    }

    /** Bot calls are possible (token + guild configured). */
    public function enabled(): bool
    {
        return ! empty($this->token) && ! empty($this->guildId);
    }

    /**
     * Guild scheduled events currently on the server.
     *
     * @return array<int, array<string, mixed>>
     */
    public function scheduledEvents(): array
    {
        if (! $this->enabled()) {
            return [];
        }

        $response = $this->client()->get(self::BASE."/guilds/{$this->guildId}/scheduled-events");

        return $response->successful() ? $response->json() : [];
    }

    /**
     * Create a guild scheduled event of type EXTERNAL.
     *
     * @return array<string, mixed>|null
     */
    public function createScheduledEvent(string $name, \DateTimeInterface $start, \DateTimeInterface $end, ?string $description = null): ?array
    {
        if (! $this->enabled()) {
            return null;
        }

        $response = $this->client()->post(self::BASE."/guilds/{$this->guildId}/scheduled-events", [
            'name' => $name,
            'privacy_level' => 2, // GUILD_ONLY
            'scheduled_start_time' => $start->format(\DateTimeInterface::ATOM),
            'scheduled_end_time' => $end->format(\DateTimeInterface::ATOM),
            'entity_type' => 3, // EXTERNAL
            'entity_metadata' => ['location' => config('discord.event_location', 'MBLAN26')],
            'description' => $description ? mb_substr($description, 0, 1000) : null,
        ]);

        if (! $response->successful()) {
            Log::warning('Discord scheduled event create failed', ['status' => $response->status(), 'body' => $response->body()]);

            return null;
        }

        return $response->json();
    }

    /**
     * Update the times of an existing guild scheduled event.
     */
    public function modifyScheduledEvent(string $eventId, \DateTimeInterface $start, \DateTimeInterface $end): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        return $this->client()->patch(self::BASE."/guilds/{$this->guildId}/scheduled-events/{$eventId}", [
            'scheduled_start_time' => $start->format(\DateTimeInterface::ATOM),
            'scheduled_end_time' => $end->format(\DateTimeInterface::ATOM),
        ])->successful();
    }

    /**
     * Give a guild member the configured member role.
     */
    public function addMemberRole(string $discordUserId): bool
    {
        $roleId = config('discord.member_role_id');

        if (! $this->enabled() || empty($roleId) || empty($discordUserId)) {
            return false;
        }

        try {
            $response = $this->client()
                ->put(self::BASE."/guilds/{$this->guildId}/members/{$discordUserId}/roles/{$roleId}");

            // 204 = added, 404 = member not in the guild (nothing we can do).
            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('Discord role sync error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Post a message to a channel as the bot, optionally with components (buttons).
     *
     * @param  array<int, mixed>  $components
     * @return array<string, mixed>|null
     */
    public function postMessage(string $channelId, string $content, array $components = []): ?array
    {
        if (empty($this->token) || empty($channelId)) {
            return null;
        }

        $payload = ['content' => $content];
        if ($components !== []) {
            $payload['components'] = $components;
        }

        $response = $this->client()->post(self::BASE."/channels/{$channelId}/messages", $payload);

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Register (overwrite) the guild slash commands.
     *
     * @param  array<int, array<string, mixed>>  $commands
     */
    public function registerGuildCommands(array $commands): bool
    {
        if (! $this->enabled() || empty($this->applicationId)) {
            return false;
        }

        $response = $this->client()
            ->put(self::BASE."/applications/{$this->applicationId}/guilds/{$this->guildId}/commands", $commands);

        if (! $response->successful()) {
            Log::warning('Discord command registration failed', ['status' => $response->status(), 'body' => $response->body()]);

            return false;
        }

        return true;
    }

    private function client(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bot '.$this->token,
            'Content-Type' => 'application/json',
        ])->timeout((int) config('discord.webhook_timeout', 10));
    }
}
