<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        
        // Structure the response to match the frontend expectations
        return response()->json([
            'general' => [
                'app_name' => $settings->get('app_name', 'E-TAXE AFRIQUE'),
                'app_slogan' => $settings->get('app_slogan', 'Gestion de Dashboard de Mairie'),
                'currency' => $settings->get('currency', 'XOF'),
            ],
            'security' => [
                'twoFactor' => (bool) $settings->get('twoFactor', false),
                'sessionTimeout' => (int) $settings->get('sessionTimeout', 60),
            ],
            'finances' => [
                'tmoney_enabled' => (bool) $settings->get('tmoney_enabled', true),
                'tmoney_key' => $settings->get('tmoney_key', '••••••••••••••••'),
                'flooz_enabled' => (bool) $settings->get('flooz_enabled', false),
                'flooz_key' => $settings->get('flooz_key', ''),
                'penalty_rate' => (int) $settings->get('penalty_rate', 10),
                'grace_period' => (int) $settings->get('grace_period', 5),
            ],
            'notifications' => [
                'template_body' => $settings->get('template_body', "Bonjour {NOM}, votre paiement de {MONTANT} FCFA pour la taxe {TYPE} a bien été enregistré le {DATE}. Merci."),
            ]
        ]);
    }

    public function store(Request $request)
    {
        // Handle settings JSON if sent as part of FormData
        $data = $request->has('settings') ? json_decode($request->input('settings'), true) : $request->all();
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            Setting::updateOrCreate(['key' => 'logo_path'], ['value' => $path]);
        }

        // Flatten the nested structure for storage
        $settingsToSave = [];
        
        foreach (['general', 'security', 'finances', 'notifications'] as $section) {
            if (isset($data[$section])) {
                foreach ($data[$section] as $key => $value) {
                    // Skip logo_path as it's handled above if uploaded, 
                    // or we keep the existing one if not uploaded
                    if ($key === 'logo_path' && !$request->hasFile('logo')) continue;
                    $settingsToSave[$key] = $value;
                }
            }
        }

        foreach ($settingsToSave as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : $value]
            );
        }

        return response()->json(['message' => 'Paramètres enregistrés avec succès']);
    }

    public function auditLogs()
    {
        $logs = AuditLog::orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user' => $log->user_name,
                    'action' => $log->action,
                    'type' => $log->type,
                    'module' => $log->module,
                    'date' => $log->created_at->format('d/m/Y H:i'),
                    'ip' => $log->ip_address,
                ];
            });

        return response()->json($logs);
    }

public function backup()
{
    $database = env('DB_DATABASE');
    $username = env('DB_USERNAME');
    $password = env('DB_PASSWORD');
    $host = env('DB_HOST');

    $filename = "backup-" . now()->format('Y-m-d-H-i-s') . ".sql";
    $path = storage_path("app/" . $filename);

    // Mets le chemin exact de mysqldump.exe (XAMPP)
    $mysqldumpPath = "C:\\xampp\\mysql\\bin\\mysqldump.exe";

    if (!file_exists($mysqldumpPath)) {
        return response()->json([
            'message' => 'mysqldump.exe introuvable',
            'path_tested' => $mysqldumpPath
        ], 500);
    }

    $command = "\"{$mysqldumpPath}\" --user=\"{$username}\" --password=\"{$password}\" --host=\"{$host}\" \"{$database}\" > \"{$path}\"";

    exec($command . " 2>&1", $output, $returnVar);

    if ($returnVar !== 0) {
        Log::error("Erreur mysqldump", [
            'command' => $command,
            'output' => $output,
            'returnVar' => $returnVar
        ]);

        return response()->json([
            'message' => 'Erreur lors de la génération du backup.',
            'details' => $output
        ], 500);
    }

    if (!file_exists($path)) {
        return response()->json(['message' => 'Backup non généré'], 404);
    }

    return response()->download($path)->deleteFileAfterSend(true);
}
}
