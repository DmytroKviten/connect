<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // devices: додаємо mac, якщо його немає
        Schema::table('devices', function (Blueprint $t) {
            if (!Schema::hasColumn('devices', 'mac')) {
                $t->string('mac', 32)->nullable()->unique()->after('uid');
            }
            if (!Schema::hasColumn('devices', 'ip')) {
                $t->string('ip', 64)->nullable()->index()->after('name');
            }
        });

        // одноразові токени
        Schema::create('setup_tokens', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('token', 128)->unique();
            $t->timestamp('expires_at');
            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('setup_tokens');
        Schema::table('devices', function (Blueprint $t) {
            if (Schema::hasColumn('devices', 'mac')) $t->dropColumn('mac');
            // ip залишимо, якщо вже використовуєш
        });
    }
};
