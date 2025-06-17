<?php

namespace App\Models;

use App\Traits\Taggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    /** @use HasFactory<\Database\Factories\MediaFactory> */
    use HasFactory, Taggable;

    protected $fillable = [
        'type',
        'file_path',
        'edition_id',
    ];

    public function edition()
    {
        return $this->belongsTo(Edition::class);
    }
}
