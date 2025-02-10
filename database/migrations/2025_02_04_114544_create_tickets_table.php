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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('priority');
            $table->string('username');
            $table->string('subject');
            $table->string('error_type');
            $table->text('error_details')->nullable();
            $table->string('file')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('match_id')->nullable(); 
            $table->foreign('match_id')->references('id')->on('matches')->nullable(); 
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->date('ticket_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
