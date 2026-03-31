<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log successful write operations (POST, PUT, PATCH, DELETE)
        if ($response->isSuccessful() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $user = Auth::user();
            
            // Skip logging for auth actions (login/logout)
            $path = $request->path();
            if (str_contains($path, 'login') || str_contains($path, 'logout')) {
                return $response;
            }

            // Determine the action based on method
            $action = match ($request->method()) {
                'POST' => 'Création',
                'PUT', 'PATCH' => 'Modification',
                'DELETE' => 'Suppression',
                default => 'Action'
            };

            // Determine the type
            $type = match ($request->method()) {
                'POST' => 'create',
                'PUT', 'PATCH' => 'update',
                'DELETE' => 'delete',
                default => 'info'
            };

            // Get module from route path
            $segments = $request->segments();
            // Assuming api routes look like: api/module/...
            $module = isset($segments[1]) ? ucfirst($segments[1]) : 'API';
            
            // Clean up module name (e.g., 'payements' -> 'Payements')
            $module = str_replace('-', ' ', $module);
            $module = ucwords($module);

            AuditLog::create([
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : 'Utilisateur Mobile',
                'action' => "$action dans $module",
                'type' => $type,
                'module' => $module,
                'ip_address' => $request->ip(),
                'details' => [
                    'path' => $path,
                    'method' => $request->method(),
                    'input' => $request->except(['password', 'password_confirmation', 'logo']),
                    'status' => $response->getStatusCode()
                ]
            ]);
        }

        return $response;
    }
}
