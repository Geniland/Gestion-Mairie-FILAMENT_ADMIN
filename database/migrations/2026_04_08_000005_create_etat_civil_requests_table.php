<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etat_civil_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->string('nom');
            $table->string('telephone',50);
            $table->string('email');
            $table->enum('type',['naissance','deces','mariage','rectification']);
            $table->text('details')->nullable();
            $table->json('files')->nullable();
            $table->enum('status',['en_attente','validé','rejeté'])->default('en_attente');
            $table->string('document_url')->nullable();
            $table->text('commentaire_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etat_civil_requests');
    }
};

