<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();   // MAC / serial
            $table->string('name')->nullable();
            $table->string('brand')->nullable();   // Shelly
            $table->string('model')->nullable();   // Plus Plug S

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
