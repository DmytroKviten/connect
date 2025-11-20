<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mode_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('mode', 40);
            $table->decimal('confidence', 5, 2)->default(0); // 0..1
            $table->json('payload')->nullable();
            $table->timestamp('predicted_at')->useCurrent();
            $table->timestamps();

            $table->index(['device_id', 'predicted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mode_predictions');
    }
};
