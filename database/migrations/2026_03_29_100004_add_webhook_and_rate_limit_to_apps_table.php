<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apps', function (Blueprint $table) {
            $table->string('webhook_url', 500)->nullable()->after('allowed_origins');
            $table->unsignedInteger('rate_limit_per_minute')->nullable()->after('webhook_url');
        });
    }

    public function down(): void
    {
        Schema::table('apps', function (Blueprint $table) {
            $table->dropColumn(['webhook_url', 'rate_limit_per_minute']);
        });
    }
};

