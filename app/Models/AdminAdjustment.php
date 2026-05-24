<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AdminAdjustment extends Model
{
    protected $table = 'admin_adjustments';

    protected $fillable = [
        'admin_id',
        'user_id',
        'amount_cents',
        'reason',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
