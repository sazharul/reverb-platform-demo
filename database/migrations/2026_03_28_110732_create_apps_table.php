<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('app_id', 32)->unique();
            $table->string('app_key', 64)->unique();
            $table->string('app_secret', 64);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('max_connections')->default(200);
            $table->json('allowed_origins')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index(['app_key', 'is_active']);  // hot path — every WS connect
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apps');
    }
};
