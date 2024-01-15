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
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('season_id');
            $table->unsignedInteger('week');
            $table->unsignedInteger('home_team_id');
            $table->unsignedInteger('home_goals')->nullable();
            $table->unsignedInteger('away_team_id');
            $table->unsignedInteger('away_goals')->nullable();
            $table->integer('played')->default(0); // 1 = Played, 2 = Not played
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
