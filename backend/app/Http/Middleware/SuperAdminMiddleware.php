<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     * Only allow super-admin to access Laravel backend routes
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return redirect('/login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }
        // Check if user has super-admin or admin role
        /** @var User $user */
        $user = Auth::user();
        if (! ($user->hasRole('super-admin') || $user->hasRole('admin'))) {
            abort(403, __('dashboard.access_denied'));
        }

        return $next($request);
    }
}
