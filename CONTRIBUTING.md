# Contributing to SavvyGoose

Thanks for your interest in contributing. This is a small, opinionated project — the bar is "does it earn its complexity?" rather than "is it possible?". Bug fixes, small UX polish, and well-scoped new tools are the most useful contributions.

## Before you start

- For anything bigger than a bug fix or a small tweak, **open an issue first** describing what you want to do and why. It's easier to redirect at the idea stage than after a PR is up.
- Check [`config/tools.php`](config/tools.php) — the tool list is the canonical inventory. If you're proposing a new tool, make sure it doesn't overlap with an existing one.
- Read [CLAUDE.md](CLAUDE.md). It documents the architectural constraints (no database, single layout, URL-as-state convention, Vite entry points) that PRs need to respect.

## Local setup

See [README.md](README.md#local-development). The short version:

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
composer dev
```

You'll need a Flux Pro license to install dependencies — without it, `composer install` will fail on the Flux Pro package.

## Adding a new tool

A tool is a static Blade page with Alpine-driven interactivity. There are four steps:

1. **Register it** in [`config/tools.php`](config/tools.php). Add an entry under `tools` with `slug`, `name`, `tagline`, `category`, and `icon`. Use an existing category key, or add a new one to the `categories` array. Icons are a tagged union: either `['type' => 'flux', 'name' => 'icon-name']` for a Flux icon, or `['type' => 'image', 'src' => 'image/foo.png']` for a PNG in `public/image/`.

2. **Add the Blade view** at `resources/views/{slug}.blade.php`. Wrap it in `<x-layouts.app>`. The route is registered automatically from the registry — you don't need to touch `routes/web.php`.

3. **Add the Alpine data** at `resources/js/data/{slug}.js` (camelCased export) and register it in `resources/js/app.js`:

   ```js
   import { mySlug } from './data/my-slug';
   Alpine.data('mySlug', mySlug);
   ```

4. **Add a smoke test** to `tests/Feature/DashboardTest.php` so the route at minimum returns 200 and shows the tool name.

### Tool design conventions

- **Run in the browser.** Tools should not POST user input back to the server. Hashing, encoding, parsing — all client-side.
- **URL-as-state for sharable tools.** If a tool has meaningful state worth sharing or bookmarking, mirror it to the query string. The Barcode Generator (`resources/js/data/barcode.js`) is the reference implementation: read params on `init()`, push state changes back via a `$watch` + `updateUrl()` helper, and surface a share-URL field.
- **No build-time secrets.** Anything a user puts into a tool is theirs — don't ship it to analytics, don't ship it to your dev console.

## Code style

- **PHP**: `vendor/bin/pint` before committing. CI runs Pint and will fail on style violations.
- **Blade**: the project's `.bladeformatterrc.json` registers `flux:`, `livewire:`, and `x-` as component prefixes. If you use the Blade Formatter, point it at this config or `flux:*` tags will get mis-indented.
- **JavaScript**: keep Alpine data files small and self-contained. Lean on Alpine reactivity rather than imperative DOM manipulation.

## Tests

```bash
vendor/bin/pest                              # full suite
vendor/bin/pest tests/Feature/DashboardTest.php
vendor/bin/pest --filter=name
```

Tests bind `Tests\TestCase` via Pest. **Do not** add `->use(RefreshDatabase::class)` — there is no database, and `RefreshDatabase` triggers `php artisan migrate` in `setUp()`, which breaks the whole suite.

## Pull requests

- One concern per PR. A new tool, a bug fix, and a refactor should be three PRs, not one.
- Run Pint and the test suite locally before pushing — CI will catch you anyway, but the round-trip is slower.
- Write a commit message that explains *why*, not *what*. The diff already shows what changed.
- If your change is user-visible, include a screenshot or short video in the PR description.

## Reporting bugs

Open an issue with:

- The tool affected (slug or URL)
- What you did, what you expected, and what happened
- Browser and OS, if it might be relevant (especially for the Image Resizer, Barcode Generator, or anything that touches Canvas or printing)

## License

By contributing, you agree that your contributions will be licensed under the project's [MIT License](LICENSE).
