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
        Schema::create('vendor_matches', function (Blueprint $table) {
            $table->id();
            $table->integer('m5MatchId')->unique();
            $table->integer('m5SeriesId');
            $table->string('m5SeriesName');
            $table->year('m5Year');
            $table->string('m5TeamA');
            $table->string('m5TeamB');
            $table->string('m5MatchNo');
            $table->dateTime('m5StartDate')->nullable();
            $table->dateTime('m5EndDate')->nullable();
            $table->integer('m5MatchStatusId');
            $table->string('m5MatchStatus');
            $table->string('m5MatchFormat');
            $table->string('m5MatchResult')->nullable();
            $table->string('m5GenderName')->nullable();
            $table->string('m5OneBattingTeamName')->nullable();
            $table->string('m5OneScoresFull')->nullable();
            $table->string('m5TwoBattingTeamName')->nullable();
            $table->string('m5TwoScoresFull')->nullable();
            $table->string('m5GroundName');
            $table->string('m5TeamAShortName')->nullable();
            $table->string('m5TeamBShortName')->nullable();
            $table->string('m5TeamALogo')->nullable();
            $table->string('m5TeamBLogo')->nullable();
            $table->string('m5Commentary')->nullable();
            $table->string('m5CompetitionName')->nullable();
            $table->string('m5CompetitionId')->nullable();
            $table->string('m5GroundId')->nullable();
            $table->time('m5MatchStartTimeGMT');
            $table->time('m5MatchStartTimeLocal');
            $table->time('m5MatchStartTimeDubai');
            $table->boolean('isActive');
            $table->string('m5SeriesType')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_matches');
    }
};
