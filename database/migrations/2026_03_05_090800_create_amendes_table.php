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
      Schema::create('amendes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('commune_id')
                ->constrained('communes')
                ->cascadeOnDelete();

            $table->foreignId('contribuable_id')
                ->constrained('contribuables')
                ->cascadeOnDelete();

            $table->string('motif');

            $table->decimal('montant', 15, 2);

            $table->string('statut')->default('non_payee');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amendes');
    }
};
