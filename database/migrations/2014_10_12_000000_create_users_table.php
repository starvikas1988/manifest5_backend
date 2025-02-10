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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phoneno')->nullable();
            $table->enum('role', ['admin', 'operator', 'user'])->default('user');
            $table->string('profile_image')->nullable();
            $table->string('device_id')->nullable();
            $table->string('host_name')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->string('otp')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
