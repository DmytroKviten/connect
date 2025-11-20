<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('measurements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('device_id')->constrained()->cascadeOnDelete();
            $t->decimal('power_w', 10, 3)->nullable();      // потужність
            $t->decimal('voltage_v', 10, 3)->nullable();    // напруга
            $t->decimal('current_a', 10, 3)->nullable();    // струм
            $t->decimal('energy_wh', 12, 3)->nullable();    // енергія
            $t->decimal('temperature_c', 5, 2)->nullable(); // температура
            $t->timestamps(); // created_at = час вимірювання
            $t->index(['device_id', 'created_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('measurements');
    }
};
