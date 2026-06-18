<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Agents;
use Illuminate\Support\Facades\Hash;

$agent = Agents::find(6); // Maire Zio 1

echo "=== Test du mot de passe pour Maire Zio 1 ===\n";
echo "Email: {$agent->email}\n";
echo "Rôle: {$agent->role}\n";
echo "Hash stocké: {$agent->password}\n\n";

// Testons quelques mots de passe courants
$testPasswords = ['password', '123456', 'maire123', 'admin123'];

foreach ($testPasswords as $pwd) {
    $check = Hash::check($pwd, $agent->password);
    echo "Test '{$pwd}': " . ($check ? '✅ CORRECT' : '❌ INCORRECT') . "\n";
}

echo "\n---\n";
echo "Pour réinitialiser le mot de passe à 'maire123', exécutez:\n";
echo "php artisan tinker --execute=\"\$agent = \\App\\Models\\Agents::find(6); \$agent->password = 'maire123'; \$agent->save(); echo 'Mot de passe mis à jour !';\"";
