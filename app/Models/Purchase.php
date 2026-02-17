<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'referrer_id',
        'plan_key',
        'plan_name',
        'daily_interest_bps',
        'duration_days',
        'min_amount_cents',
        'max_amount_cents',
        'amount_cents',
        'referral_commission_cents',
        'interest_earned_cents',
        'accrued_days',
        'last_interest_at',
        'payment_method',
        'status',
    ];

    protected $casts = [
        'referral_commission_cents' => 'integer',
        'interest_earned_cents' => 'integer',
        'accrued_days' => 'integer',
        'last_interest_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
