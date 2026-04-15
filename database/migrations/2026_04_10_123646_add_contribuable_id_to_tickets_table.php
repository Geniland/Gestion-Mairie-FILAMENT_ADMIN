<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('contribuable_id')
                ->nullable()
                ->after('taxe_id') // adapte selon ta table
                ->constrained('contribuables')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['contribuable_id']);
            $table->dropColumn('contribuable_id');
        });
    }
};
