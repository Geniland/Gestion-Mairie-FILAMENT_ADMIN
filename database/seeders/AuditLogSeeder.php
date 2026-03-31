<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuditLog;
use Carbon\Carbon;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $logs = [
            [
                'user_name' => 'Admin Marc',
                'action' => 'Modification Taxe',
                'type' => 'update',
                'module' => 'Types Taxes',
                'ip_address' => '192.168.1.15',
                'created_at' => Carbon::now()->subHours(2)
            ],
            [
                'user_name' => 'Agent Koffi',
                'action' => 'Suppression Contribuable',
                'type' => 'delete',
                'module' => 'Contribuables',
                'ip_address' => '192.168.1.22',
                'created_at' => Carbon::now()->subHours(5)
            ],
            [
                'user_name' => 'Admin Marc',
                'action' => 'Connexion',
                'type' => 'login',
                'module' => 'Auth',
                'ip_address' => '192.168.1.15',
                'created_at' => Carbon::now()->subDays(1)
            ],
            [
                'user_name' => 'Maire Jean',
                'action' => 'Export Rapport',
                'type' => 'export',
                'module' => 'Rapports',
                'ip_address' => '10.0.0.5',
                'created_at' => Carbon::now()->subDays(1)->subHours(2)
            ],
            [
                'user_name' => 'System',
                'action' => 'Backup Automatique',
                'type' => 'system',
                'module' => 'Backup',
                'ip_address' => '127.0.0.1',
                'created_at' => Carbon::now()->subDays(2)
            ]
        ];

        foreach ($logs as $log) {
            AuditLog::create($log);
        }
    }
}
