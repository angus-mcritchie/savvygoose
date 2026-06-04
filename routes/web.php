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

// Private internal pages — not in the tool registry, dashboard, nav, or sitemap.
Route::view('/on-it-rc-header-card', 'on-it-rc-header-card')->name('on-it-rc-header-card');
Route::view('/atrek-rc-header-card', 'atrek-rc-header-card')->name('atrek-rc-header-card');

Route::get('/sitemap.xml', function () {
    $base = rtrim(config('app.url'), '/');

    $urls = [['loc' => $base.'/', 'changefreq' => 'weekly', 'priority' => '1.0']];

    foreach (array_keys(config('tools.categories')) as $key) {
        $urls[] = ['loc' => $base.'/'.$key, 'changefreq' => 'weekly', 'priority' => '0.8'];
    }

    foreach (config('tools.tools') as $tool) {
        $urls[] = ['loc' => $base.'/'.$tool['slug'], 'changefreq' => 'monthly', 'priority' => '0.7'];
    }

    return response()
        ->view('sitemap', ['urls' => $urls])
        ->header('Content-Type', 'application/xml');
})->name('sitemap');

Route::get('/api/holidays', HolidaysController::class)->name('api.holidays');
