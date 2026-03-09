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
        Schema::create('contribuables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('commune_id')
                ->constrained('communes')
                ->cascadeOnDelete();

                

            $table->string('nom');
            $table->string('telephone');
            $table->string('type'); // individu ou entreprise
            $table->string('numero_identifiant')->nullable();
            $table->string('adresse')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contribuables');
    }
};
