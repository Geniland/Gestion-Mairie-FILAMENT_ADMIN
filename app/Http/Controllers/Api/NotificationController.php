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

        // Si c'est l'admin, il voit tous les messages pour pouvoir gérer les conversations
        if ($sender['type'] === 'admin') {
            $notifications = Notification::with('user')
                ->orderBy('created_at', 'asc')
                ->get();
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
            $targetUserId = $data['user_id'];
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

        // citoyen ne peut modifier que ses messages
        if ($sender['type'] === 'citizen') {
            if ($notification->user_id !== $sender['user']->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Accès refusé'
                ], 403);
            }
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Marqué comme lu',
        ]);
    }
}