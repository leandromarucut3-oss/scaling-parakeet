<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageSlotCapacity extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_key',
        'slot_capacity',
    ];
}
