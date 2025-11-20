<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModePrediction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'mode',
        'confidence',
        'payload',
        'predicted_at',
    ];

    protected $casts = [
        'payload'      => 'array',
        'predicted_at' => 'datetime',
        'confidence'   => 'float',
    ];
}
