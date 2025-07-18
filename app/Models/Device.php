<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Device extends Model
{
    use HasFactory;

    /** масове присвоєння */
    protected $fillable = [
        'uid',
        'name',
        'ip',
        'brand',
        'model',
        'user_id',   // ← обов’язково!
    ];

    /* ───────────  ЗВ’ЯЗКИ  ─────────── */

    /** Пристрій належить користувачу */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** У пристрою багато вимірів */
    public function readings(): HasMany
    {
        return $this->hasMany(Reading::class);
    }

    /** Останній вимір (power / voltage / energy) */
    public function latestReading(): HasOne
    {
        return $this->hasOne(Reading::class)
                    ->latestOfMany('taken_at');
    }
}
