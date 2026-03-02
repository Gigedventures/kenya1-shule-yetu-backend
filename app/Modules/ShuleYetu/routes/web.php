<?php

use App\Modules\ShuleYetu\Http\Controllers\SchoolSwitchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::get('/shule/schools/select', [SchoolSwitchController::class, 'index'])
        ->name('shule.schools.select');

    Route::get('/shule/schools/switch/{code}', [SchoolSwitchController::class, 'switch'])
        ->name('shule.schools.switch.get');

    Route::post('/shule/schools/switch/{code}', [SchoolSwitchController::class, 'switch'])
        ->name('shule.schools.switch');
});
