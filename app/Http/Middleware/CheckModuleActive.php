<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleActive
{
    /**
     * Handle an incoming request.
     *
     * Usage:
     * Route::get('/twende', ...)->middleware('module.active:twende');
     */
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        $module = DB::table('modules')->where('slug', $slug)->first();

        // If module not found, abort
        if (!$module) {
            abort(404, 'Module not found.');
        }

        // If module exists but is not active → show Coming Soon page
        if (!$module->is_active) {
            return redirect()->route('module.coming', ['slug' => $slug]);
        }

        // Module is active → allow access
        return $next($request);
    }
}
