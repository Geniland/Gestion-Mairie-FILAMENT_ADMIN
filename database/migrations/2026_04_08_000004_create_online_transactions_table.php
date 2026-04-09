<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxe_id')->constrained('taxes')->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount',15,2)->default(0);
            $table->string('fedapay_transaction_id')->nullable();
            $table->enum('status',['en_attente','success','cancel','rejeté','validé'])->default('en_attente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_transactions');
    }
};

