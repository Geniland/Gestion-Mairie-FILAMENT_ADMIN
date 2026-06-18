<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private function getAuthSender(Request $request)
    {
        // On vérifie d'abord si c'est un agent via son guard spécifique
        if (auth('api_agents')->check()) {
            return [
                'user' => auth('api_agents')->user(),
                'type' => 'admin'
            ];
        }

        // Sinon on vérifie si c'est un citoyen
        if (auth('api_users')->check()) {
            return [
                'user' => auth('api_users')->user(),
                'type' => 'citizen'
            ];
        }

        return null;
    }

    public function index(Request $request)
    {
        $sender = $this->getAuthSender($request);

        if (!$sender) {
            return response()->json(['status' => false, 'message' => 'Non authentifié'], 401);
        }

        // Si c'est l'admin/agent
        if ($sender['type'] === 'admin') {
            $admin = $sender['user'];
            
            // Super admin voit tous les messages
            if ($admin->isSuperAdmin()) {
                $notifications = Notification::with('user')
                    ->orderBy('created_at', 'asc')
                    ->get();
            } 
            // Les autres agents voient seulement les messages de leur commune
            else {
                $notifications = Notification::with('user')
                    ->whereHas('user', function($query) use ($admin) {
                        $query->where('commune_id', $admin->commune_id);
                    })
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
        } 
        // Si c'est un citoyen, il ne voit que son propre fil de discussion
        else {
            $notifications = Notification::where('user_id', $sender['user']->id)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return response()->json([
            'status' => true,
            'data' => $notifications,
        ]);
    }

    public function store(Request $request)
    {
        $sender = $this->getAuthSender($request);

        if (!$sender) {
            return response()->json([
                'status' => false,
                'message' => 'Non authentifié'
            ], 401);
        }

        $data = $request->validate([
            'message' => 'required|string',
            'title' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $senderType = $sender['type'];
        $senderId = $sender['user']->id;

        // user_id dans la table notifications est TOUJOURS l'ID du citoyen (table users)
        if ($senderType === 'admin') {
            if (empty($data['user_id'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'L\'ID du citoyen est obligatoire pour un admin'
                ], 400);
            }
            
            $admin = $sender['user'];
            $targetUserId = $data['user_id'];
            
            // Vérification : seul le super admin peut écrire à tous les citoyens
            if (!$admin->isSuperAdmin()) {
                $targetUser = \App\Models\User::find($targetUserId);
                if (!$targetUser || $targetUser->commune_id !== $admin->commune_id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Vous ne pouvez pas envoyer de message à ce citoyen (commune différente)'
                    ], 403);
                }
            }
        } else {
            // Un citoyen s'écrit à lui-même (son fil)
            $targetUserId = $sender['user']->id;
        }

        $notification = Notification::create([
            'user_id' => $targetUserId,
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'title' => $data['title'] ?? ($senderType === 'admin'
                ? "Message de l'administration"
                : "Message du citoyen"
            ),
            'message' => $data['message'],
            'is_read' => false,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Message envoyé',
            'data' => $notification->load('user'),
        ], 201);
    }

    public function markAsRead(Request $request, $id)
    {
        $sender = $this->getAuthSender($request);

        if (!$sender) {
            return response()->json([
                'status' => false,
                'message' => 'Non authentifié'
            ], 401);
        }

        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json([
                'status' => false,
                'message' => 'Notification introuvable'
            ], 404);
        }

        if ($sender['type'] === 'citizen') {
            // Citoyen ne peut modifier que ses messages
            if ($notification->user_id !== $sender['user']->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Accès refusé'
                ], 403);
            }
        } else {
            // Agent ne peut modifier que les messages de sa commune (sauf super admin)
            $admin = $sender['user'];
            if (!$admin->isSuperAdmin()) {
                $targetUser = \App\Models\User::find($notification->user_id);
                if (!$targetUser || $targetUser->commune_id !== $admin->commune_id) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Accès refusé'
                    ], 403);
                }
            }
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Marqué comme lu',
        ]);
    }
}