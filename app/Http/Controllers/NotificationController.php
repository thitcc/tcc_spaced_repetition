<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->get();
        
        return response()->json($notifications);
    }

    public function store(Request $request)
    {
        $notification = new Notification;
        $notification->user_id = Auth::id();
        $notification->content = $request->content;
        $notification->read_at = null;
        $notification->save();

        return response()->json($notification, 201);
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);

        if ($notification && $notification->user_id == Auth::id()) {
            $notification->read_at = now();
            $notification->save();
            return;
        }

        return response()->json(['message' => 'Notificação não encontrada.'], 404);
    }
}
