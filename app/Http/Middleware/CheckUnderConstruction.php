<?php

namespace App\Http\Middleware;

use App\Support\BlogSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * When "Under construction" is switched on in Dashboard → Settings, every public request gets a
 * 503 holding page. Authenticated (dashboard) users bypass the check so they can preview the
 * blog normally, and the dashboard itself (including its login) are never affected.
 */
class CheckUnderConstruction
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            BlogSettings::underConstruction()
            && ! $request->user()
            && ! $request->is('dashboard/*', 'dashboard')
        ) {
            return response()->view('errors.503', [], 503);
        }

        return $next($request);
    }
}
