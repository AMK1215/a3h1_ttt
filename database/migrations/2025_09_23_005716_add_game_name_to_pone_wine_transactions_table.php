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
        Schema::table('pone_wine_transactions', function (Blueprint $table) {
            $table->string('game_name')->nullable()->after('player_agent_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pone_wine_transactions', function (Blueprint $table) {
            $table->dropColumn('game_name');
        });
    }
};
