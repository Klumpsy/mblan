<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    /** @use HasFactory<\Database\Factories\MediaFactory> */
    use HasFactory;

    protected $fillable = [
        'type',
        'file_path',
        'edition_id',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function edition()
    {
        return $this->belongsTo(Edition::class);
    }
}
