<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $role)
    {
        $user = $request->user()->load('role');

        //  Si admin, il a accès à tout
        if ($user->role->libelle === 'administrateur') {
            return $next($request);
        }

        //  Sinon, on vérifie le rôle
        if ($user->role->libelle !== $role) {
            return response()->json([
                'message' => 'Accès non autorisé. Rôle requis : ' . $role
            ], 403);
        }

        return $next($request);
    }
}

