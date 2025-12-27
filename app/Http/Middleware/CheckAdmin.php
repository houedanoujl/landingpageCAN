<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect('/admin/login')->with('error', 'Veuillez vous connecter en tant qu\'administrateur.');
        }
        
        $user = User::find($userId);
        
        // Autoriser les rôles admin et soboa
        if (!$user || !in_array($user->role, ['admin', 'soboa'])) {
            session()->forget('user_id');
            return redirect('/admin/login')->with('error', 'Accès non autorisé. Vous devez être administrateur.');
        }
        
        return $next($request);
    }
}
