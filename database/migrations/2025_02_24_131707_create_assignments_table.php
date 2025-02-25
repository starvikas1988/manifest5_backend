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
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('operator_id')->nullable()->constrained('users')->onDelete('cascade'); // Nullable operator
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade'); // Nullable category
            $table->foreignId('match_id')->nullable()->constrained('matches')->onDelete('cascade'); // Nullable match
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['operator_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['match_id']);
            $table->dropColumn(['operator_id', 'category_id', 'match_id']);;
        });
    }
};
