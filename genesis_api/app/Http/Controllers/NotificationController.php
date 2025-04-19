<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Récupère les notifications par ordre décroissant de date
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Notifications récupérées avec succès.',
            'notifications' => $notifications
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $notification = $user->notifications()->create([
            'titre' => $validated['titre'],
            'message' => $validated['message'],
            'lu' => false,
        ]);

        return response()->json([
            'message' => 'Notification créée avec succès.',
            'notification' => $notification
        ], 201);
    }

    public function markAsRead($id)
    {
        $notification = \App\Models\Notification::find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification introuvable'], 404);
        }

        $notification->lu = true;
        $notification->save();

        return response()->json([
            'message' => 'Notification marquée comme lue.',
            'notification' => $notification
        ]);
    }
}

