<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per slash command a player runs via the Discord bot. Powers the
 * command-usage widget on the admin dashboard.
 */
class DiscordCommandLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'command',
        'discord_user_id',
    ];
}
