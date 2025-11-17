<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateLastActivity
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $now = time();
            // Throttle updates to avoid excessive writes
            if (!isset($user->last_activity) || ($user->last_activity?->getTimestamp() ?? 0) < ($now - 60)) {
                $user->last_activity = $now;
                // Save quietly to avoid triggering observers
                $user->save();
            }
        }

        return $next($request);
    }
}