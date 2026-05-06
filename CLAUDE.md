# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

SavvyGoose is a Laravel 13 site offering free utility tools (barcode generator, percentage calculator, character counter). It descends from the Laravel Livewire starter kit but the **auth, settings, User model, and database layer have been removed** — the app is intentionally stateless. All tools are static Blade views with interactivity driven by Alpine.js. Livewire's JS is still loaded so `wire:navigate` works for SPA-like navigation between pages.

## No database

This app has no database connection. Sessions and cache use the `file` driver (`.env`); queue is `sync`. **Don't add `DB_CONNECTION` to `.env`, don't run `php artisan migrate`, and don't add migrations** — there's an empty `database/migrations/.gitkeep` to keep the directory present for `artisan`. `database/database.sqlite` should not exist; if it appears, something queried the DB by mistake.

If a feature genuinely needs persistence later, reintroduce a real driver in `.env` and add migrations — don't quietly let SQLite get auto-created.

## Common commands

- `composer dev` — server + `pail` (logs) + Vite dev concurrently. (No `queue:listen`; queue is sync.)
- `php artisan serve` — Laravel dev server only.
- `npm run dev` / `npm run build` — Vite dev / production build.
- `vendor/bin/pint` — PHP code style. CI runs this as the lint step.
- `vendor/bin/pest` (or `php artisan test`, or `vendor/bin/phpunit`) — run the test suite.
- `vendor/bin/pest tests/Feature/DashboardTest.php` — single file. `vendor/bin/pest --filter=name` for a single test.

Tests bind `Tests\TestCase` via Pest (`tests/Pest.php`) — **no `RefreshDatabase`**. If a code formatter ever adds `->use(RefreshDatabase::class)` back, remove it; it triggers `php artisan migrate` in `setUp()` and breaks the whole suite because there's no DB.

## Architecture

### Adding a tool

1. Add a route in `routes/web.php` as `Route::view('/foo', 'foo')->name('foo')`.
2. Add the top-level Blade view at `resources/views/foo.blade.php`. Wrap content in `<x-layouts.app>`.
3. Add an Alpine data file at `resources/js/data/foo.js` and register it in `resources/js/app.js` via `Alpine.data('foo', foo)`.
4. Link it from the dashboard tile (`resources/views/dashboard.blade.php`) **and** the header navbar (`resources/views/components/layouts/app/header.blade.php` — both `flux:navbar` desktop and `flux:sidebar`/`flux:navlist` mobile).
5. Add a smoke test in `tests/Feature/DashboardTest.php`.

### URL-as-state convention

The barcode generator (`resources/js/data/barcode.js`) treats the URL query string as canonical state: `init()` reads params from the URL, `$watch` hooks push state changes back via `updateUrl()`, and the share-URL field surfaces this. The `?print=true` param triggers `printBarcode()` on load. Preserve this pattern when extending tools.

### Layouts and Flux

- `resources/views/components/layouts/app.blade.php` is the only layout — wraps content in `<flux:main>` plus the header from `layouts/app/header.blade.php`.
- Flux Pro is a paid composer package served from `https://composer.fluxui.dev` (configured in `composer.json` `repositories`). CI authenticates via `FLUX_USERNAME` / `FLUX_LICENSE_KEY` GitHub secrets. Local installs need `auth.json` with the same credentials.

### Vite entry points

`vite.config.js` has three inputs: `resources/css/app.css`, `resources/js/app.js`, and a separate `resources/css/barcode-generator.css`. The barcode CSS is standalone because `Printd` injects it into the print iframe via `Vite::asset(...)` — keep it as its own input.

### Blade formatting

`.bladeformatterrc.json` registers `flux:` (and `livewire:`, `x-`) as component prefixes. Use the Blade Formatter respecting this config; otherwise `flux:*` tags get mis-indented.

## Deployment (Laravel Cloud)

This is the deployment target. The app is fully stateless so most cloud concerns vanish, but a few things matter:

- **Don't enable a "run migrations" deploy step** — there's no DB and no migrations.
- **Don't attach a managed database** in the Cloud project — nothing reads from it, and a stray `DB_CONNECTION=mysql` env var would just attempt and fail connections.
- Octane is the runtime on Cloud. Booting must remain DB-free; a stray `DB::` call would either hang on connection attempts or fall back to creating a per-container `database/database.sqlite` that doesn't survive deploys.
- File-driver sessions/cache are per-container. With one container that's fine. If you ever scale to multiple containers and add anything stateful (e.g. flash messages that must survive a load-balanced redirect), switch `SESSION_DRIVER` and `CACHE_STORE` to `redis` (Cloud provides managed Redis) — don't fall back to `database`, because we don't have one.
- `laravel/nightwatch` is installed; it ships telemetry to Laravel Cloud automatically. No app config needed.
