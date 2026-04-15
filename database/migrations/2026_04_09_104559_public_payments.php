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
       Schema::create('public_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('public_taxe_id')->constrained('public_taxes')->onDelete('cascade');

            $table->decimal('montant', 12, 2);
            $table->string('reference')->unique();

            $table->string('transaction_id')->nullable();
            $table->string('checkout_url')->nullable();

            $table->enum('status', ['en_attente', 'validé', 'rejeté', 'failed'])->default('en_attente');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
