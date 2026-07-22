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

// Static trust / info pages.
Route::view('/about', 'pages.about')->name('about');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/contact', 'pages.contact')->name('contact');

// Private internal pages — not in the tool registry, dashboard, nav, or sitemap.
Route::view('/on-it-rc-header-card', 'on-it-rc-header-card')->name('on-it-rc-header-card');
Route::view('/atrek-rc-header-card', 'atrek-rc-header-card')->name('atrek-rc-header-card');

Route::get('/sitemap.xml', function () {
    $base = rtrim(config('app.url'), '/');

    // lastmod from the max mtime of the page's Blade view and the tool registry,
    // so a content edit to either bumps the freshness signal Google uses.
    $configMtime = is_file(config_path('tools.php')) ? filemtime(config_path('tools.php')) : time();
    $lastmod = function (string $view) use ($configMtime) {
        $full = resource_path('views/'.$view);
        $times = [$configMtime];
        if (is_file($full)) {
            $times[] = filemtime($full);
        }

        return date('Y-m-d', max($times));
    };

    $urls = [[
        'loc' => $base.'/',
        'changefreq' => 'weekly',
        'priority' => '1.0',
        'lastmod' => $lastmod('dashboard.blade.php'),
    ]];

    foreach (array_keys(config('tools.categories')) as $key) {
        $urls[] = ['loc' => $base.'/'.$key, 'changefreq' => 'weekly', 'priority' => '0.8', 'lastmod' => $lastmod('category.blade.php')];
    }

    foreach (config('tools.tools') as $tool) {
        $urls[] = ['loc' => $base.'/'.$tool['slug'], 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => $lastmod($tool['slug'].'.blade.php')];
    }

    foreach (['about', 'privacy', 'contact'] as $page) {
        $urls[] = ['loc' => $base.'/'.$page, 'changefreq' => 'yearly', 'priority' => '0.3', 'lastmod' => $lastmod('pages/'.$page.'.blade.php')];
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
