<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GameVacationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est connecté
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Vérifier si l'utilisateur est en mode vacances
        if ($user->vacation_mode) {
            // Rediriger vers la page de profil avec un message
            return redirect()->route('dashboard.profile')->with('error', 'Vous ne pouvez pas accéder au jeu pendant que vous êtes en mode vacances.');
        }

        return $next($request);
    }
}