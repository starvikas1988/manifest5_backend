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
        Schema::table('users', function (Blueprint $table) {
            $table->date('effective_date')->nullable()->after('status');
            $table->date('cease_date')->nullable()->after('effective_date');
            $table->timestamp('email_verified_at')->nullable()->after('cease_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('effective_date');
            $table->dropColumn('cease_date');
        });
    }
};
