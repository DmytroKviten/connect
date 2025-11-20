<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid',
        'mac',
        'ip',
        'name',
        'brand',
        'model',
        'category',
        'user_id',
        'last_seen_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    /* ---------- Relations ---------- */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(Reading::class);
    }

    /**
     * Останній вимір (без урізання select — щоб уникнути "ambiguous column")
     * Використовуємо зв'язок "one of many" за максимальною датою + id як тай-брейкер.
     */
    public function latestReading(): HasOne
    {
        return $this->hasOne(Reading::class)
            ->ofMany(
                ['taken_at' => 'max', 'id' => 'max'],
                function ($q) {
                    // ВАЖЛИВО: не робити тут ->select([...]),
                    // Laravel сам підставить повністю кваліфіковані колонки.
                }
            );
    }

    /* ---------- Scopes ---------- */

    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /* ---------- Normalizers (mutators) ---------- */

    /**
     * Зберігаємо MAC у форматі A–F0–9 (UPPER), без розділювачів.
     */
    protected function mac(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if ($value === null || $value === '') return null;
                $mac = strtoupper($value);
                return preg_replace('/[^A-F0-9]/', '', $mac);
            }
        );
    }

    /**
     * Порожній IP -> null (щоб не плодити порожні рядки).
     */
    protected function ip(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value !== null && $value !== '' ? (string)$value : null
        );
    }
}
