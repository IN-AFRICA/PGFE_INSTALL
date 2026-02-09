<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;

final class ActionsController extends Controller
{
    /**
     * Show a list of web routes and expose UI actions (buttons/forms) for them.
     * The view will only render action controls when the current user is allowed
     * according to the route's permission middleware or when no permission is required.
     */
    public function index(Request $request)
    {
        $routes = collect(RouteFacade::getRoutes())->map(function ($route) {
            // extract middleware list
            $middleware = $route->gatherMiddleware();

            // try to find a permission or role:middleware declaration
            $permission = null;
            foreach ($middleware as $m) {
                // middleware strings can be like 'permission:users.edit' or 'role:super-admin'
                if (str_starts_with($m, 'permission:')) {
                    $permission = explode(':', $m, 2)[1] ?? null;
                    break;
                }
                if (str_starts_with($m, 'role_or_permission:') || str_starts_with($m, 'role:')) {
                    // keep role/role_or_permission token for information (not used as can() permission)
                    $permission = $m;
                    // do not break - prefer explicit permission: middleware
                }
            }

            return [
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'methods' => $route->methods(),
                'action' => $route->getActionName(),
                'middleware' => $middleware,
                'permission' => $permission,
                'wants_csrf' => in_array('POST', $route->methods()) || in_array('PUT', $route->methods()) || in_array('PATCH', $route->methods()) || in_array('DELETE', $route->methods()),
            ];
        })
        // keep only web routes (exclude closures with no name? we keep named and admin web routes)
            ->filter(fn ($r) => collect($r['methods'])->contains('GET') || collect($r['methods'])->contains('POST'))
            ->values();

        // Optionally filter to admin area or any prefix if desired
        // $routes = $routes->filter(fn($r) => str_starts_with($r['uri'], 'api/') === false);

        return view('backend.pages.settings.actions', [
            'routes' => $routes,
        ]);
    }
}
