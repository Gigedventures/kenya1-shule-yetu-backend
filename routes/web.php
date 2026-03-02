<?php

use Illuminate\Support\Facades\Route;
use App\Models\Module;

/*
|--------------------------------------------------------------------------
| Kenya 1 Homepage
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    // Fetch modules from DB as OBJECTS (important)
    $modules = Module::orderBy('display_order')->get();

    return view('home', compact('modules'));
});


/*
|--------------------------------------------------------------------------
| Module Loader
|--------------------------------------------------------------------------
*/

Route::get('/module/{slug}', function ($slug) {

    $module = Module::where('slug', $slug)->firstOrFail();

    // If module is OFF → show Coming Soon page
    if (!$module->is_active) {
        return view('modules.coming-soon', compact('module'));
    }

    // If module is ON → load real module entry
    return match ($slug) {
        'shule-yetu' => view('shule-yetu.dashboard', compact('module')),
        'kenya-cademy' => view('kenya-cademy.dashboard', compact('module')),
        'my-chamaa' => view('my-chamaa.dashboard', compact('module')),
        default => abort(404),
    };

});
