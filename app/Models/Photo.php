<?php

namespace App\Models;

use App\Models\Concerns\BelongsToEdition;
use App\Models\Concerns\HasReactions;
use App\Models\Concerns\OptimizesImages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    use BelongsToEdition;
    use HasFactory;
    use HasReactions;
    use OptimizesImages;

    protected $fillable = [
        'edition_id',
        'user_id',
        'image',
        'story',
        // Settable so the admin can backfill old editions with the original date.
        'created_at',
    ];

    /** @var array<int, string> */
    protected array $optimizableImages = ['image'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
