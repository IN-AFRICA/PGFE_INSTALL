<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class InjectSchoolId
{
    /**
     * Inject the authenticated user's school_id into the request when missing.
     * Applied on write requests (POST, PUT, PATCH) to avoid repeating logic in controllers.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            // Only inject if the incoming payload does not contain school_id
            // and the authenticated user has a school_id set.
            if (! $request->filled('school_id') && isset($user->school_id)) {
                $request->merge(['school_id' => $user->school_id]);
            }
        }

        return $next($request);
    }
}
