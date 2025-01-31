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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->date('match_date');
            $table->time('match_time');
            $table->string('status');
            $table->string('series_name');
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('team_name');
            $table->string('captain_image');
            $table->string('status');
            $table->timestamps();
            $table->unsignedBigInteger('match_id')->nullable(); 
            $table->foreign('match_id')->references('id')->on('matches')->nullable(); 
        });

        Schema::create('match_team', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('match_id')->nullable(); 
            $table->unsignedBigInteger('team_id')->nullable(); 

            $table->foreign('match_id')->references('id')->on('matches')->onDelete('cascade')->nullable(); 
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade')->nullable(); 

            $table->unique(['match_id', 'team_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_team');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('matches');
    }
};
