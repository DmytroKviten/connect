<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        // Laravel 10+: автоматичне хешування пароля
        'password'          => 'hashed',
    ];

    /** Пристрої користувача */
    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'user_id');
    }

    /** Короткоживучі токени для провіжингу */
    public function setupTokens(): HasMany
    {
        return $this->hasMany(SetupToken::class, 'user_id');
    }

    /** Скоуп: користувачі з кількістю пристроїв */
    public function scopeWithDevicesCount($q)
    {
        return $q->withCount('devices');
    }

    /** Утиліта: створити одноразовий setup-токен (30 хв) */
    public function createSetupToken(int $ttlMinutes = 30): SetupToken
    {
        // Почистимо прострочені
        $this->setupTokens()->where('expires_at', '<', now())->delete();

        return $this->setupTokens()->create([
            'token'      => \Str::random(48),
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);
    }
}
