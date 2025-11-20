<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('setup_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('setup_tokens', 'device_mac')) {
                $table->string('device_mac', 17)->nullable()->after('token');
            }
            if (!Schema::hasColumn('setup_tokens', 'used_at')) {
                $table->timestamp('used_at')->nullable()->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('setup_tokens', function (Blueprint $table) {
            if (Schema::hasColumn('setup_tokens', 'device_mac')) {
                $table->dropColumn('device_mac');
            }
            if (Schema::hasColumn('setup_tokens', 'used_at')) {
                $table->dropColumn('used_at');
            }
        });
    }
};
