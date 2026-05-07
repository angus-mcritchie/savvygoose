# SavvyGoose

A small collection of free, browser-based developer and everyday utilities — barcode and QR generators, JSON/regex tools, hashes, unit and timestamp conversion, and more. No accounts, no tracking, no server-side processing of your input.

Live: <https://savvygoose.com>

## What's inside

Every tool runs entirely in your browser. The Laravel app just serves static Blade pages; Alpine.js handles the interactivity.

- **Text & Writing** — Character Counter, Case Converter, Diff Viewer, Markdown ↔ HTML Converter
- **Numbers & Time** — Percentage Calculator, Unit Converter, Timestamp Converter, Time Between Dates
- **Data & Encoding** — Base64, URL Encoder, JWT Decoder, Hash Generator, Color Converter, JSON Formatter, Regex Tester, Browser Info
- **Generators** — Barcode (Code 128), QR Code, Password, UUID (v4/v7), Image Resizer, Slug Generator, Lorem Ipsum

The full registry lives in [`config/tools.php`](config/tools.php) — that file is the single source of truth for routes, the dashboard, and navigation.

## Stack

- **Laravel 13** on PHP 8.4, served via **Octane** in production
- **Livewire Flux** (Pro) for UI primitives, **Tailwind 4** for styling
- **Alpine.js** for per-page interactivity (Livewire's JS is loaded so `wire:navigate` provides SPA-like transitions, but no Livewire components are used)
- **Vite** for the asset pipeline
- **Pest** for tests, **Pint** for code style
- Deployed to **Laravel Cloud**

There is no database. Sessions and cache use the file driver; the queue is `sync`. See [CLAUDE.md](CLAUDE.md) for the rationale and the constraints that keeps in place.

## Local development

### Prerequisites

- PHP 8.4 with the usual Laravel extensions
- Composer 2
- Node.js 22+
- A Flux Pro license (the UI library is paid). Add an `auth.json` at the project root:

  ```json
  {
      "http-basic": {
          "composer.fluxui.dev": {
              "username": "your-flux-username",
              "password": "your-flux-license-key"
          }
      }
  }
  ```

### Setup

```bash
git clone git@github.com:angus-mcritchie/savvygoose.git
cd savvygoose
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### Run it

```bash
composer dev
```

That runs `php artisan serve`, `php artisan pail` (logs), and `npm run dev` concurrently. Visit <http://localhost:8000>.

If you only want one of those, `php artisan serve` and `npm run dev` work standalone.

### Tests & lint

```bash
vendor/bin/pest          # full suite
vendor/bin/pest --filter=name   # one test
vendor/bin/pint          # PHP code style (CI runs this)
```

The test suite does **not** use `RefreshDatabase` — there's no database. If something re-introduces it, remove it; it'll break the whole suite.

## Deploying

The app is deployed to Laravel Cloud and is intentionally stateless. A few things to keep in mind:

- Don't enable a "run migrations" deploy step — there are no migrations.
- Don't attach a managed database — nothing reads from one.
- File-driver sessions/cache are per-container. With a single container that's fine; if you ever scale out and need shared state, switch `SESSION_DRIVER` and `CACHE_STORE` to `redis`.

The full set of constraints is in [CLAUDE.md](CLAUDE.md).

## Contributing

Bug reports, fixes, and new tools are welcome. See [CONTRIBUTING.md](CONTRIBUTING.md) for the workflow and the steps to add a tool.

## License

[MIT](LICENSE) — except for the Livewire Flux Pro dependency, which is a paid commercial package and is not redistributed in this repository.
