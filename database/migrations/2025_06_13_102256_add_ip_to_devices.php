<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $t) {
            $t->string('ip')->nullable()->after('uid');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $t) {
            $t->dropColumn('ip');
        });
    }
};
