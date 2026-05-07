<?php

use App\Http\Controllers\HolidaysController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard')->name('dashboard');

foreach (array_keys(config('tools.categories')) as $categoryKey) {
    Route::view('/'.$categoryKey, 'category', ['categoryKey' => $categoryKey])
        ->name('category.'.$categoryKey);
}

foreach (config('tools.tools') as $tool) {
    Route::view('/'.$tool['slug'], $tool['slug'])->name($tool['slug']);
}

Route::get('/api/holidays', HolidaysController::class)->name('api.holidays');
