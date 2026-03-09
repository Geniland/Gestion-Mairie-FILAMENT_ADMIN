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
        Schema::create('agents', function (Blueprint $table) {

        $table->id();

        $table->foreignId('commune_id')
            ->constrained('communes')
            ->cascadeOnDelete();

        $table->string('nom');

        $table->string('telephone');

        $table->string('password');

        $table->string('email')->nullable();

        $table->enum('role', [
            'super_admin',
            'admin_commune',
            'agent'
        ]);

       

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
