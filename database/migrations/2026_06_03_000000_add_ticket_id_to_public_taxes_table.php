<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_taxes', function (Blueprint $table) {
            $table->foreignId('ticket_id')->nullable()->after('commentaire_admin')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('public_taxes', function (Blueprint $table) {
            $table->dropForeign(['ticket_id']);
            $table->dropColumn('ticket_id');
        });
    }
};