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

            $table->foreignId('taxe_id')
                ->constrained('taxes')
                ->cascadeOnDelete();

            $table->foreignId('commune_id')
                ->constrained('communes')
                ->cascadeOnDelete();

            $table->string('numero_ticket')->unique();

            $table->string('qr_hash');

            $table->date('date_expiration');

            $table->string('statut')->default('valide');

            $table->timestamps();
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
