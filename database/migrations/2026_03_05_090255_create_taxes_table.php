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
        Schema::create('taxes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('commune_id')
                ->constrained('communes')
                ->cascadeOnDelete();

            $table->foreignId('contribuable_id')
                ->constrained('contribuables')
                ->cascadeOnDelete();

            $table->foreignId('type_taxe_id')
                ->constrained('types_taxes')
                ->cascadeOnDelete();


            $table->foreignId('agent_id')
                ->nullable()
                ->constrained('agents')
                ->nullOnDelete();

            $table->decimal('montant', 15, 2);

            $table->date('periode_debut');
            $table->date('periode_fin');

            $table->string('statut')->default('en_attente');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
