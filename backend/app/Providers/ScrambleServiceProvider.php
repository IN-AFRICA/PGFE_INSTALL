<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Illuminate\Support\ServiceProvider;

class ScrambleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Grouper la doc par préfixe de route (premier segment après v1)
        Scramble::resolveTagsUsing(function ($routeInfo, $operation) {
            $uri = $routeInfo->route->uri;
            $segments = explode('/', $uri);
            // Cherche le segment juste après 'v1' et le sous-segment (ex: v1/accounting/fees => Accounting/Fees)
            $tag = 'Autres';
            for ($i = 0; $i < count($segments); $i++) {
                if ($segments[$i] === 'v1') {
                    $main = isset($segments[$i+1]) ? ucfirst($segments[$i+1]) : null;
                    $sub = isset($segments[$i+2]) ? ucfirst($segments[$i+2]) : null;
                    if ($main && $sub) {
                        $tag = $main . '/' . $sub;
                    } elseif ($main) {
                        $tag = $main;
                    }
                    break;
                }
            }
            return [$tag];
        });
    }
}
