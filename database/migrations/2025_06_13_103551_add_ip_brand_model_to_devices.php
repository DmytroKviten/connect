<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $t) {
            // додаємо ip, якщо ще не існує
            if (!Schema::hasColumn('devices', 'ip')) {
                $t->string('ip')->nullable()->after('uid');
            }

            // додаємо brand, якщо ще не існує
            if (!Schema::hasColumn('devices', 'brand')) {
                $t->string('brand')->nullable()->after('ip');
            }

            // додаємо model, якщо ще не існує
            if (!Schema::hasColumn('devices', 'model')) {
                $t->string('model')->nullable()->after('brand');
            }
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $t) {
            // у зворотньому напрямку видаляємо лише ті стовпці,
            // які зараз існують
            foreach (['ip', 'brand', 'model'] as $col) {
                if (Schema::hasColumn('devices', $col)) {
                    $t->dropColumn($col);
                }
            }
        });
    }
};

