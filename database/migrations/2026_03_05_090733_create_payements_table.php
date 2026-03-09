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
       Schema::create('payements', function (Blueprint $table) {

            $table->id();

            $table->foreignId('taxe_id')
            ->constrained('taxes')
            ->cascadeOnDelete();

            $table->foreignId('commune_id')
            ->constrained('communes')
            ->cascadeOnDelete();

        
      

            $table->decimal('montant', 15, 2);

            $table->string('mode_payement');

            $table->string('reference')->nullable();

            $table->date('date_payement');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payements');
    }
};
