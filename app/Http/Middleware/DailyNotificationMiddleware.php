<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use Carbon\Carbon;

class DailyNotificationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $now = Carbon::now();

            // Check if this is the user's first activity of the day
            if (!$user->last_login_at || Carbon::parse($user->last_login_at)->toDateString() != $now->toDateString()) {
                Notification::create([
                    'user_id' => $user->id,
                    'content' => "Você tem atividades pendentes, clique aqui para visualizar!",
                    'read_at' => null,
                ]);

                $user->last_login_at = $now;
                $user->save();
            }
        }

        return $next($request);
    }
}
