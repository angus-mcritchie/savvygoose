<?php

use App\Http\Controllers\Auth\GithubAuthController;
use App\Http\Controllers\HolidaysController;
use App\Http\Controllers\StarDependenciesController;
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

Route::get('/auth/github/redirect', [GithubAuthController::class, 'redirect'])->name('auth.github.redirect');
Route::get('/auth/github/callback', [GithubAuthController::class, 'callback'])->name('auth.github.callback');
Route::post('/auth/github/disconnect', [GithubAuthController::class, 'disconnect'])->name('auth.github.disconnect');

Route::post('/api/star-dependencies/resolve', [StarDependenciesController::class, 'resolve'])->name('api.star-dependencies.resolve');
Route::post('/api/star-dependencies/star', [StarDependenciesController::class, 'star'])->name('api.star-dependencies.star');
