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
        Schema::table('event_logs', function (Blueprint $table) {
            $table->string('source', 20)->default('api')->after('sender_socket_id');
            $table->index(['app_id', 'source', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_logs', function (Blueprint $table) {
            $table->dropIndex(['app_id', 'source', 'created_at']);
            $table->dropColumn('source');
        });
    }
};
