<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard')->name('dashboard');

foreach (config('tools.tools') as $tool) {
    Route::view('/'.$tool['slug'], $tool['slug'])->name($tool['slug']);
}
