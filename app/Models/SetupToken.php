<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetupToken extends Model
{
    protected $fillable = ['user_id', 'token', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',

    ];

    // Якщо у таблиці є created_at/updated_at — залишай як є.
    // Якщо їх немає у міграції — розкоментуй наступний рядок:
    // public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Перевірка строку дії */
    public function isValid(): bool
    {
        return (bool) $this->expires_at && $this->expires_at->isFuture();
    }

    /** Скоуп: тільки чинні токени (не прострочені) */
    public function scopeActive($q)
    {
        return $q->where('expires_at', '>', now());
    }

    /** Скоуп: токени конкретного користувача */
    public function scopeForUser($q, int $userId)
    {
        return $q->where('user_id', $userId);
    }

    /** Зробити одноразовим — зручно викликати після успішного пінгу */
    public function revoke(): void
    {
        // найпростіше — видалити токен
        $this->delete();
    }
}
