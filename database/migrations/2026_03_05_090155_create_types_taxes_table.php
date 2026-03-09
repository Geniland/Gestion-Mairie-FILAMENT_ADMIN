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
        Schema::create('types_taxes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('commune_id')
                ->constrained('communes')
                ->cascadeOnDelete();

            $table->string('nom');
            $table->text('description')->nullable();

            // montant de base de la taxe (optionnel)
            $table->decimal('montant_base', 15, 2)->nullable();

            // périodicité (journalier, mensuel, annuel)
            $table->string('periode')->nullable();

            $table->boolean('actif')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('types_taxes');
    }
};
