<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('setup_tokens', function (Blueprint $table) {
            // ставимо індекси ТІЛЬКИ якщо є така колонка
            if (Schema::hasColumn('setup_tokens', 'expires_at')) {
                $table->index('expires_at', 'setup_tokens_expires_at_idx');
            }
            if (Schema::hasColumn('setup_tokens', 'device_mac')) {
                $table->index('device_mac', 'setup_tokens_device_mac_idx');
            }
            if (Schema::hasColumn('setup_tokens', 'used_at')) {
                $table->index('used_at', 'setup_tokens_used_at_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('setup_tokens', function (Blueprint $table) {
            // дропаємо за іменами (правильний спосіб для dropIndex)
            if (Schema::hasColumn('setup_tokens', 'expires_at')) {
                $table->dropIndex('setup_tokens_expires_at_idx');
            }
            if (Schema::hasColumn('setup_tokens', 'device_mac')) {
                $table->dropIndex('setup_tokens_device_mac_idx');
            }
            if (Schema::hasColumn('setup_tokens', 'used_at')) {
                $table->dropIndex('setup_tokens_used_at_idx');
            }
        });
    }
};
