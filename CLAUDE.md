# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

NutriTrace (repo: Esprit_5twin7_BeatTheHeat) — a Laravel 12 application (PHP ^8.2) with a Blade + Vite + Tailwind CSS v4 frontend. No domain code exists yet; `welcome.blade.php` is a minimal placeholder page.

## Styling rules (mandatory)

- Colors are **HSL only**. Never write hex codes, `rgb()`/`rgba()`, `oklch()` or named colors in CSS, Blade, JS or inline SVG.
- Every color is a CSS variable defined once in `resources/css/app.css` (light values on `:root`, dark values in the `prefers-color-scheme: dark` override) and exposed to Tailwind through `@theme inline` as `--color-*`.
- Tailwind's default palette is disabled (`--color-*: initial`), so classes like `bg-red-500` or `bg-white` do not exist. Use the token classes (`bg-background`, `text-foreground`, `bg-surface`, `text-muted`, `border-border`, `bg-primary`, `text-accent`, `text-danger`, …). Never use arbitrary values such as `bg-[#fff]` or `text-[hsl(...)]`.
- When a new color is needed, add a semantic token (with both a light and a dark value) to `app.css` instead of hard-coding it at the point of use.
- Keep the project clean: no leftover Laravel boilerplate, dead code or unused assets.

## Setup

`.env` and `database/database.sqlite` are gitignored, so a fresh clone returns HTTP 500 (`MissingAppKeyException`) until these steps are run:

```
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate        # creates database/database.sqlite if missing
npm run build              # or keep `npm run dev` running
```

Views load assets only through `@vite`; without a build or a running Vite dev server, pages fail with a missing-manifest error.

Sessions, cache and queue all use the `database` driver (see `.env`), so the app also fails if migrations haven't been run.

## Commands

- `composer dev` — runs the server, queue listener, Pail log tailer and Vite dev server together (via `concurrently`)
- `php artisan serve` / `npm run dev` — run the backend or the frontend separately
- `npm run build` — build production assets
- `php artisan test` — run all tests (PHPUnit 11; suites: `tests/Unit`, `tests/Feature`)
- `php artisan test --filter=TestName` — run one test class or method; `php artisan test tests/Feature/ExampleTest.php` runs one file
- `vendor/bin/pint` — format PHP (Laravel Pint); `vendor/bin/pint --test` only checks
- `php artisan pail` — tail application logs; the log file is `storage/logs/laravel.log`

## Architecture notes

- Laravel 12 streamlined structure: middleware, exception handling and routing are configured in `bootstrap/app.php` (there is no `app/Http/Kernel.php`), and service providers are registered in `bootstrap/providers.php`.
- Routes: `routes/web.php` (HTTP) and `routes/console.php` (Artisan commands/schedule). There is no `routes/api.php`; run `php artisan install:api` to add it.
- Frontend entry points are `resources/css/app.css` and `resources/js/app.js` (defined in `vite.config.js`). Tailwind v4 is configured through CSS (`@import "tailwindcss"`), not through a `tailwind.config.js`.
- Tests: the SQLite in-memory settings in `phpunit.xml` are commented out, so tests that touch the database use the development `database/database.sqlite`. Using `RefreshDatabase` will wipe dev data unless you uncomment those lines.
