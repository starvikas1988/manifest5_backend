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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); 
            $table->string('name');
            $table->unsignedBigInteger('category_id')->nullable(); 
            $table->string('status');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->nullable(); 
            $table->foreign('category_id')->references('id')->on('categories')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
