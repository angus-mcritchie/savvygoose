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

The tool list is driven by `config/tools.php` — routes, the dashboard, and both nav locations all iterate over it. Routes are registered automatically from the registry, so you never need to touch `routes/web.php`.

**1. Register the tool** — append to `config/tools.php` `tools` array:

```php
[
    'slug' => 'word-counter',
    'name' => 'Word Counter',
    'tagline' => 'Count words, sentences, and paragraphs.',
    'category' => 'text', // existing key from `categories`, or add a new one
    'icon' => ['type' => 'flux', 'name' => 'hashtag'],
],
```

Icons are a tagged union: `['type' => 'image', 'src' => 'image/foo.png']` for PNGs in `public/image/`, or `['type' => 'flux', 'name' => 'code-bracket-square']` for a Flux icon. `<x-tool-icon>` handles both.

**2. Create the Alpine data file** at `resources/js/data/{slug-camelCased}.js`. Wrap your factory in `withUrlState` so anything shareable lands in the URL automatically:

```js
import { withUrlState } from '../lib/urlState';

const schema = {
    text: { type: 'string', maxLength: 3000 },
    mode: { type: 'enum', values: ['lower', 'upper'], default: 'lower' },
};

export default withUrlState(schema, () => ({
    // Local state (not in URL) goes here.
    error: '',

    // Optional: extra init runs AFTER schema parsing + watchers are wired.
    init() {
        this.$watch('text', () => this.compute());
        this.compute();
    },

    get output() { /* ... */ },
    clear() { this.text = ''; },
}));
```

`withUrlState` adds `url`, `urlTooLong`, `initFromUrl()`, and `updateUrl()` to the component, and watches every schema key. **Don't write your own `initFromUrl`/`updateUrl` — extend the schema instead.**

Schema entry options: `type` (`string` | `number` | `integer` | `boolean` | `enum` | `color`), `default`, `alias` (URL key if it differs from the JS key), `min`/`max` (numeric), `maxLength` (string), `values` (enum), and custom `parse(raw, state)` / `serialize(value, state)` for cross-field rules. See `resources/js/data/barcode.js` and `resources/js/data/regexTester.js` for patterns.

**3. Register the Alpine component** in `resources/js/app.js`:

```js
import wordCounter from './data/wordCounter';
// ...
Alpine.data('wordCounter', wordCounter);
```

**4. Create the Blade view** at `resources/views/{slug}.blade.php`. Use this skeleton:

```blade
<x-layouts.app>
    <div
        class="mx-auto max-w-[1200px]"
        x-data="wordCounter"
        x-on:keydown.window.escape="clear()"  {{-- optional shortcuts --}}
    >
        {{-- Header --}}
        <div class="mb-8 flex justify-center">
            <div class="grid grid-cols-[auto_1fr] items-center gap-4">
                <flux:icon.hashtag class="size-20 text-zinc-700 dark:text-zinc-200" />
                <div>
                    <flux:heading class="mb-1" level="1" size="xl">Word Counter</flux:heading>
                    <flux:heading class="font-normal opacity-70" level="2">
                        Count words, sentences, and paragraphs.
                    </flux:heading>
                </div>
            </div>
        </div>

        <div class="grid gap-6">
            {{-- Tool body --}}
            <div class="rounded-lg border border-black/10 p-8 dark:border-white/10">
                <flux:textarea x-model="text" label="Text" rows="6" />

                <div class="mt-4 flex gap-2">
                    <x-copy-button
                        value="output"
                        flash="'wc-output'"
                        size="sm"
                        x-bind:disabled="!output"
                    />
                    <flux:button
                        x-on:click="$download(output, 'output.txt')"
                        x-bind:disabled="!output"
                        icon="arrow-down-tray"
                        size="sm"
                    >
                        .txt
                    </flux:button>
                </div>
            </div>

            {{-- Share field — drop in as the last card --}}
            <x-share-field
                class="rounded-lg border border-black/10 p-8 dark:border-white/10"
                subheading="The URL below carries your input and settings."
                tooLongMessage="Input is too long to include in the URL."
            />
        </div>
    </div>
    <x-tool-content />
</x-layouts.app>
```

`<x-tool-content />` renders the howto and FAQ sections sourced from `config/tools.php` — keep it at the end of every tool view so the SEO content lands on the page.

**5. Add a smoke test** in `tests/Feature/DashboardTest.php`:

```php
test('the word counter renders', function () {
    $this->get('/word-counter')->assertOk()->assertSee('Word Counter');
});
```

### Tool copy voice

Tool copy lives in `config/tools.php` (`tagline`, `meta.title`, `meta.description`, `howto`, `faqs`). The site has been scrubbed of AI-flavored phrasing more than once — keep new entries in the same voice. Hard rules:

- **No em-dash as a connector in body copy.** They're fine in titles via the SEO helper (`Tool Name — Savvy Goose`) and as conversational asides in Blade views, but in `config/tools.php` body strings they're a strong AI tell when stacked. Use periods, colons, parens, or a comma instead. Em-dash count in `config/tools.php` should be ~0.
- **Don't open FAQ answers with `Yes —` or `No —`.** Just answer. `Yes. Both seconds and milliseconds are auto-detected.` reads like a person; `Yes — both seconds and milliseconds are auto-detected.` reads like a model.
- **Don't repeat the same privacy stamp on every entry.** "Runs in your browser", "never leaves your device", "no upload" are useful, but if they appear verbatim in 15 descriptions in a row it's boilerplate. Vary the wording or drop it where it's already obvious from context.
- **Avoid triple-adjective lists** like "free, fast, privacy-friendly" or "free, instant, no sign-up". Pick one or two.
- **Break parallel structure across siblings.** When writing the four `category_seo` descriptions, or the meta descriptions for ten tools in a row, deliberately vary sentence shape. If they all start with `Free online X — list, list, list`, the template itself is the tell.
- **`meta.title` separator** — use a colon or parens (`Hash Generator: MD5, SHA-1, SHA-256, SHA-512`) rather than an em-dash, since `App\Support\Seo` already appends ` — Savvy Goose`.

When extending an existing tool, mirror the surrounding entries' tone — terse, concrete, second person where it helps. The Blade views' inline copy is the best reference for the house voice.

### Shared primitives reference

- **`withUrlState(schema, factory)`** in `resources/js/lib/urlState.js` — URL ↔ state binding, used by every tool with shareable settings.
- **`$copy(text, key)` magic + `$store.copy.is(key)`** — `resources/js/lib/clipboard.js`. The `<x-copy-button value="..." flash="'unique-key'" />` component wraps both; pass `flash` as an Alpine expression (e.g. `'static-key'` or `c.key` inside an `x-for`).
- **`$download(blobOrText, filename, mime?)`** magic — `resources/js/lib/download.js`. Accepts `Blob`, `ArrayBuffer`, `Uint8Array`, or string.
- **`<x-share-field />`** — drop-in Share section. Props: `subheading`, `tooLongMessage`, `heading` (pass `false` to suppress and use your own).

A few existing tools have non-trivial schemas worth mirroring:
- `resources/js/data/regexTester.js` — cross-field `serialize` (skip `test`/`replacement` if total exceeds budget).
- `resources/js/data/imageResizer.js` — conditional serialize (`quality` only included when format supports it), custom format short-codes.
- `resources/js/data/unitConverter.js` — schema defaults that depend on another field (`from`/`to` defaults change with `cat`).
- `resources/js/data/timeBetweenDates.js` — dynamic per-instance defaults (computed via `detectCountry`); user `init()` fills in if URL didn't.

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
