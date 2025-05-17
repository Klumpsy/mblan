<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'registration_detail_id',
        'payment_id',
        'amount',
        'currency',
        'status',
        'payment_method',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registrationDetail()
    {
        return $this->belongsTo(Registration::class);
    }

    public function edition()
    {
        return $this->hasOneThrough(
            Edition::class,
            Registration::class,
            'id',
            'id',
            'registration_detail_id',
            'edition_id'
        );
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }


    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }


    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }


    public function isPaid()
    {
        return $this->status === 'paid';
    }


    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function hasFailed()
    {
        return $this->status === 'failed';
    }
}
