<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next, ...$roles): Response
    {
       /*  return $next($request); */

       /* $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        foreach ($roles as $role) {
            if ($user->role === $role) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized action.');
    } */

    $user = Auth::user();

    if (!$user) {
        \Log::info('User not authenticated');
        return redirect()->route('login');
    }

    \Log::info('User role: ' . $user->role);
    \Log::info('Allowed roles: ' . implode(', ', $roles));

    foreach ($roles as $role) {
        if ($user->role === $role) {
            \Log::info('Role match found: ' . $role);
            return $next($request);
        }
    }

    \Log::info('No matching role found');
    /* abort(403, 'Unauthorized action.'); */
    return redirect()->back()
                    ->with('message', 'Unauthorized action.')
                    ->with('isError', true);
}
    
}
