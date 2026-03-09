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
    Schema::table('payements', function (Blueprint $table) {
        $table->foreignId('contribuable_id')
              ->constrained()
              ->cascadeOnDelete();
    });
}

public function down(): void
{
    Schema::table('payements', function (Blueprint $table) {
        $table->dropForeign(['contribuable_id']);
        $table->dropColumn('contribuable_id');
    });
}
};
