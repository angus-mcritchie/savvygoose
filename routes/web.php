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
    $tools = config('tools.tools');

    // lastmod comes from the declared 'updated' date on each tool, never from
    // filemtime: Laravel Cloud deploys a fresh checkout, so mtimes are all the
    // deploy time and every URL would share one date that moves on each deploy.
    // An unknown date omits <lastmod> rather than inventing one, because a
    // wrong date is worse than none — Google stops trusting the field entirely.
    // Returns the date only if it is a real calendar day in YYYY-MM-DD form.
    // createFromFormat accepts 2026-13-45 and rolls it over, so compare the
    // round-trip back to the input to reject anything that was not already valid.
    $valid = function (?string $date): ?string {
        if (! is_string($date)) {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed && $parsed->format('Y-m-d') === $date ? $date : null;
    };

    // A category or the dashboard is a listing of its tools, so it is as fresh
    // as the most recently updated tool it lists.
    $newestOf = function (array $subset) use ($valid) {
        $dates = array_filter(array_map(fn ($tool) => $valid($tool['updated'] ?? null), $subset));

        return $dates === [] ? null : max($dates);
    };

    $urls = [[
        'loc' => $base.'/',
        'changefreq' => 'weekly',
        'priority' => '1.0',
        'lastmod' => $newestOf($tools),
    ]];

    foreach (array_keys(config('tools.categories')) as $key) {
        $inCategory = array_filter($tools, fn ($tool) => $tool['category'] === $key);

        $urls[] = ['loc' => $base.'/'.$key, 'changefreq' => 'weekly', 'priority' => '0.8', 'lastmod' => $newestOf($inCategory)];
    }

    foreach ($tools as $tool) {
        $urls[] = ['loc' => $base.'/'.$tool['slug'], 'changefreq' => 'monthly', 'priority' => '0.7', 'lastmod' => $valid($tool['updated'] ?? null)];
    }

    foreach (config('tools.static_pages') as $page => $updated) {
        $urls[] = ['loc' => $base.'/'.$page, 'changefreq' => 'yearly', 'priority' => '0.3', 'lastmod' => $valid($updated)];
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
