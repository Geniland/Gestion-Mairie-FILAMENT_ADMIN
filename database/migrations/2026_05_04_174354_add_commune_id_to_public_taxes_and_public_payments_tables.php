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
        Schema::table('public_taxes', function (Blueprint $table) {
            $table->foreignId('commune_id')->nullable()->constrained('communes')->cascadeOnDelete();
        });

        Schema::table('public_payments', function (Blueprint $table) {
            $table->foreignId('commune_id')->nullable()->constrained('communes')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('public_payments', function (Blueprint $table) {
            $table->dropForeign(['commune_id']);
            $table->dropColumn('commune_id');
        });

        Schema::table('public_taxes', function (Blueprint $table) {
            $table->dropForeign(['commune_id']);
            $table->dropColumn('commune_id');
        });
    }
};
