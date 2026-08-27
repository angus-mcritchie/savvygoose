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
// The registry in config/tools.php is what also marks them noindex, so adding a
// page here without adding it there would leave it publicly indexable.
foreach (config('tools.private_pages') as $privatePage) {
    Route::view('/'.$privatePage, $privatePage)->name($privatePage);
}

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
    // Takes mixed, not ?string: config is hand-edited, and a value that is a
    // list or an int must degrade to "no date" like any other bad value rather
    // than throw a TypeError and take the whole sitemap down with a 500.
    $valid = function (mixed $date): ?string {
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

    // The routed pages are the source of truth for which URLs exist; the config
    // only supplies their dates. Driving membership off the config keys instead
    // would let a typo there drop a real page from the sitemap and advertise
    // a 404 in its place, which is a worse failure than a missing lastmod.
    foreach (['about', 'privacy', 'contact'] as $page) {
        $urls[] = ['loc' => $base.'/'.$page, 'changefreq' => 'yearly', 'priority' => '0.3', 'lastmod' => $valid(config('tools.static_pages.'.$page))];
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
