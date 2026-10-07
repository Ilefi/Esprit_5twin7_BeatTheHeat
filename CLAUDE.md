# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

NutriTrace (repo: Esprit_5twin7_BeatTheHeat) — a "farm to fork" food traceability platform, Esprit 5TWIN academic project. Laravel 12 (PHP ^8.2), Blade, Vite, Tailwind CSS v4, Alpine.js, Laravel Breeze (Blade stack). Four team modules: 1 Produits & Certifications, 2 Chaîne de traçabilité, 3 Empreinte environnementale, 4 Signalements & Avis. `docs/TEMPLATE.md` documents the UI template in detail (in French).

- User-facing text is **French**; code (classes, variables, route names, attributes) is **English**. URLs are French (`/produits`, `/admin/signalements`), route names English (`front.*`, `account.*`, `admin.*`).
- Every page reads **MySQL through Eloquent** (`app/Models`). The demo dataset comes from seeders (`database/seeders`, one per entity, called in order by `DatabaseSeeder`) that use the factories (`database/factories`) for fixed records plus generated filler. Write actions (store/update/destroy/moderate) still only validate and redirect: they are marked `// TODO(Gestion N)` for each teammate to implement in their module.

## Styling rules (mandatory)

- Colors are **HSL only**, declared once as raw channels in the `:root` block of `resources/css/nutritrace.css` (`--nt-primary: 123 46% 34%`) and used as `hsl(var(--nt-primary))` / `hsl(var(--nt-primary) / .12)`. No hex, `rgb()`, `oklch()`, named colors, or literal `hsl()` values anywhere else (CSS, Blade, JS, inline SVG). Exception: `public/favicon.svg` (standalone file, mirrors the tokens in HSL).
- Tailwind's default palette is disabled (`--color-*: initial` in the `@theme inline` block of `nutritrace.css`). Only token classes exist: `bg-primary`, `text-muted-foreground`, `bg-danger/10`, `text-primary-strong`, `bg-eco-b`… Never use arbitrary color values (`bg-[#fff]`).
- Colored text on a tinted background uses the `*-strong` tokens (AA contrast). Eco-score B/C/D chips use `text-foreground`, A uses `text-primary-foreground`, E uses `text-danger-foreground`.
- New color → add a semantic token to `:root` and map it in `@theme inline`; don't hard-code it at the point of use.
- No dynamic Tailwind class names (`'bg-eco-'.$grade`): use lookup maps of full class names. Status/priority/type/role labels and colors live only in `App\View\Components\StatusBadge::MAP` (`<x-status-badge type="report" :value="…" />`, `StatusBadge::labelFor()`, `::options()`).
- Keep the project clean: no leftover boilerplate, dead code or unused assets.

## Setup

Database is **MySQL** (XAMPP defaults in `.env.example`: `127.0.0.1:3306`, database `laravel`, user `root`, empty password — each teammate adjusts their own gitignored `.env`):

```
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed   # admin@ / actor@ / consumer@nutritrace.tn, password "password"
php artisan storage:link     # serves uploads (product images, report evidence) from the public disk
npm run build                # or keep `npm run dev` running — views load assets only through @vite
```

Sessions, cache and queue use the `database` driver, so the app fails until migrations have run. `php artisan migrate:fresh --seed` resets the demo dataset.

## Commands

- `composer dev` — server + queue listener + Pail logs + Vite dev server together
- `npm run build` — build assets (required after adding Tailwind classes when not running `npm run dev`)
- `php artisan test` — all tests; `php artisan test --filter=TemplatePagesTest` smoke-tests every GET page (guest 200, protected → /login, admin 200, consumer 403 on /admin)
- `php artisan view:cache` then `php artisan view:clear` — compile-check every Blade view
- `php artisan route:list --except-vendor`
- `vendor/bin/pint` — format PHP

Tests use in-memory SQLite (`phpunit.xml`), so `RefreshDatabase` never touches the MySQL dev database. `Tests\TestCase` sets `$seed = true`: the in-memory database is migrated and seeded once per run, each test rolls back in a transaction. Keep queries DB-agnostic (no MySQL-only SQL functions) so they run on both.

## Architecture

- **Layouts (Blade inheritance is graded — keep it explicit):** `layouts/master` (head, `@stack('styles'|'scripts')`, `@yield('body')`) ← `front` (navbar/footer, `@section('pre_footer')…@show`) ← `account`; `master` ← `admin` (sidebar/topbar, sections `page_title`, `page_subtitle`, `page_actions`, `breadcrumb`); `master` ← `auth` (Breeze views). Every page uses `@extends` + `@section`; never `<x-app-layout>`/`<x-guest-layout>`.
- **Components:** anonymous `x-nt.*` in `resources/views/components/nt/` (form fields under `nt/form/` handle label, `@error`, `old()` — pass `:use-old="false"` when several forms share field names on one page, and `bag="…"` for named error bags). Modals open with `$dispatch('open-modal', 'name')`.
- **Admin create/edit** share one partial: `@include('admin.<module>._form', ['<model>' => $model ?? null])`.
- **Routes:** `routes/front.php` (public + `/mon-espace`), `routes/admin.php` (`/admin`, middleware `['auth', 'admin']`), `routes/auth.php` (Breeze), `routes/web.php` requires them and keeps `dashboard` (redirects by role) and Breeze `profile.*` (still at `/profile`).
- **Roles:** `users.role` (`admin|actor|consumer`), `User::isAdmin()`, `admin` middleware alias → `App\Http\Middleware\EnsureUserIsAdmin` (in `bootstrap/app.php`).
- **Models & data:** seeders look records up by slug / e-mail / code (never by id) and keep the curated slugs, lot codes and report refs stable — `TemplatePagesTest` and the docs rely on them (`huile-olive-sfax`, `NT-2026-OLV-0412`, `SIG-2026-0004` owned by `consumer@`). View-facing computed attributes are model accessors (`Product::eco_score|batch_code|rating_avg|reviews_count`, `Batch::total_km|actors_count`, `Review::reply`, `Report::target|open_days`); eager-load with the provided scopes (`Product::forCards()`, `withRating()`, `orderByEcoPoints()`, `Actor::withStats()`, `Report::withTarget()`). Reports target products, actors or certifications through a `reportable` morph (`Relation::morphMap` in `AppServiceProvider`). `Impact` derives `eco_points`/`eco_score` on save; `Report` generates its `ref` on create.
- **Eco-score:** `App\Support\EcoScore` (server, also used by `Impact`) and `ntEcoPreview` in `resources/js/nutritrace/components.js` (live admin preview) implement the same formula — change both together.
- **JS:** `resources/js/nutritrace/*` registers Alpine components (`ntModal`, `ntCounter`, `ntRatingInput`, `ntFileDrop`, `ntReportWizard`, `ntChart`, `ntQr`…). Charts read colors from CSS variables via `tokens.js`. Alpine attribute expressions can't start with statements (`try`, `if`): put that logic in an `init()` method.
- Absolutely positioned children (e.g. `sr-only` labels) escape `overflow-x-auto` containers unless the container is `relative` — keep scroll containers `relative`.
- Locale is `fr` (`lang/fr/*.php`, `Carbon::setLocale` in `AppServiceProvider`, which also sets the default pagination view, the morph map and the admin view composers: `AdminSidebarComposer` for counters, `AdminNotificationsComposer` for topbar notifications).
