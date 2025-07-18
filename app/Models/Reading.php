<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reading extends Model
{
    use HasFactory;

    /* ↙️ усередині класу – OK */
    protected $fillable = [
        'device_id',
        'power_w',
        'voltage_v',
        'energy_wh',
        'taken_at',
    ];

    public $timestamps = false;   // ми самі пишемо taken_at

    /* зв'язок ↑ optional */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}
