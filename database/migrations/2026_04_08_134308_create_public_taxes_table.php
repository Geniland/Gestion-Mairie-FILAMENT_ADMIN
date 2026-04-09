<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       Schema::create('public_taxes', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->unsignedBigInteger('contribuable_id')->nullable();
    $table->string('contribuable_nom');

    $table->unsignedBigInteger('type_taxe_id');
    $table->decimal('montant', 12, 2);

    $table->date('periode_debut')->nullable();
    $table->date('periode_fin')->nullable();

    $table->string('reference')->unique();
    $table->enum('status', ['en_attente', 'approuvee', 'rejetee', 'payee'])->default('en_attente');

    $table->text('commentaire_admin')->nullable();

    $table->timestamps();

    $table->foreign('contribuable_id')->references('id')->on('contribuables')->nullOnDelete();
    $table->foreign('type_taxe_id')->references('id')->on('types_taxes')->cascadeOnDelete();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('public_taxes');
    }
};