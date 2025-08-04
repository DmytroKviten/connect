<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('devices', function (Blueprint $table) {
        $table->timestamp('last_seen_at')->nullable()->after('ip');
    });
}

public function down()
{
    Schema::table('devices', function (Blueprint $table) {
        $table->dropColumn('last_seen_at');
    });
}

};
