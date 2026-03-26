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
            $table->string('reference_transaction')->nullable()->after('date_payement');
        });
    }

    public function down(): void
    {
        Schema::table('payements', function (Blueprint $table) {
            $table->dropColumn('reference_transaction');
        });
    }
};
