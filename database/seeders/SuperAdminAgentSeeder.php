<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Agents;
use App\Models\Commune;

class SuperAdminAgentSeeder extends Seeder
{
    public function run(): void
    {
        // Vérifier s'il existe au moins une commune
        $commune = Commune::first();

        // Si aucune commune, on en crée une
        if (!$commune) {
            $commune = Commune::create([
                'nom' => 'GOLFE 7',
                'region' => 'Lomé',
            ]);
        }

        // Créer ou mettre à jour le super admin
        Agents::updateOrCreate(
            ['email' => 'genilandee@gmail.com'],
            [
                'commune_id' => $commune->id,
                'nom' => 'SUPER ADMIN',
                'telephone' => '+22890000000',
                'role' => 'super_admin',
                'password' => 'password123', // ⚠️ PAS Hash::make car ton mutator le fait déjà
                'is_blocked' => false,
                'blocked_reason' => null,
            ]
        );
    }
}