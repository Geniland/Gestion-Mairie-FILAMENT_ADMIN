<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `agents` MODIFY COLUMN `role` ENUM('super_admin', 'maire', 'agent') NOT NULL DEFAULT 'agent'");
        
        // Convertir les anciens 'admin_commune' en 'maire'
        DB::statement("UPDATE `agents` SET `role` = 'maire' WHERE `role` = 'admin_commune'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `agents` MODIFY COLUMN `role` ENUM('super_admin', 'admin_commune', 'agent') NOT NULL DEFAULT 'agent'");
        DB::statement("UPDATE `agents` SET `role` = 'admin_commune' WHERE `role` = 'maire'");
    }
};
