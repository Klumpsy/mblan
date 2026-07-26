<?php

namespace App\Models;

use App\Models\Concerns\OptimizesImages;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    use HasFactory;
    use OptimizesImages;

    protected $fillable = [
        'user_id',
        'image',
        'story',
    ];

    /** @var array<int, string> */
    protected array $optimizableImages = ['image'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
