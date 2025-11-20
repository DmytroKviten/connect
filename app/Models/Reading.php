<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reading extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'power_w',
        'voltage_v',
        'energy_wh',
        'taken_at',
        'mode',
    ];

    // У таблиці немає created_at/updated_at
    public $timestamps = false;

    // Явні типи + дата
    protected $casts = [
        'power_w'   => 'float',
        'voltage_v' => 'float',
        'energy_wh' => 'integer',
        'taken_at'  => 'datetime',
        'mode'      => 'string',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}

