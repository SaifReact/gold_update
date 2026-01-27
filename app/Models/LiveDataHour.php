<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveDataHour extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'bid_sell',
        'ask_buy',
        'low',
        'high',
    ];
}
