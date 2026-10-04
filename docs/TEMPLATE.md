# NutriTrace — Template Front Office & Back Office

> « De la ferme à l'assiette, en toute transparence »
> Projet académique Esprit · 5TWIN · Applications Web Avancées 2026-2027 · Laravel 12 + Blade + Tailwind CSS v4 + Alpine.js

Ce document décrit le template UI livré : architecture Blade, système de design, et comment brancher les vraies données de chaque module.

## Démarrage

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
# Démarrer MySQL (XAMPP) puis adapter le bloc DB_* du .env si besoin
php artisan migrate --seed      # crée les tables + le jeu de démo (seeders + factories)
npm run build                   # ou: composer dev (serveur + Vite + logs + queue)
php artisan test                # 181 tests (dont tests/Feature/TemplatePagesTest.php)
```

Base de données : **MySQL**. `.env.example` reprend la configuration par défaut de XAMPP (`127.0.0.1:3306`, base `laravel`, utilisateur `root`, mot de passe vide). Si la base n'existe pas, `php artisan migrate` propose de la créer. Pour repartir d'un jeu de démo propre : `php artisan migrate:fresh --seed`. Les tests utilisent une base SQLite en mémoire (`phpunit.xml`) et ne touchent jamais la base MySQL.

| Compte | E-mail | Mot de passe |
|---|---|---|
| Administrateur | `admin@nutritrace.tn` | `password` |
| Acteur | `actor@nutritrace.tn` | `password` |
| Consommateur | `consumer@nutritrace.tn` | `password` |

## 1. Architecture

```
resources/views/
├── layouts/
│   ├── master.blade.php      squelette HTML : <head>, polices, @vite, @stack('styles'/'scripts'), @yield('body')
│   ├── front.blade.php       @extends master  → navbar + <main> + @section('pre_footer')…@show + footer
│   ├── account.blade.php     @extends front   → espace consommateur avec navigation latérale (3 niveaux)
│   ├── admin.blade.php       @extends master  → sidebar repliable + topbar + fil d'Ariane + en-tête de page
│   └── auth.blade.php        @extends master  → écran partagé (panneau vert + carte formulaire)
├── partials/
│   ├── shared/   head, flash (@component/@slot), toasts, breadcrumb, error
│   ├── front/    navbar, mobile-menu, footer, newsletter, journey (SVG), impact-summary,
│   │             flash-container, product/filters, reviews/{block,summary,filters,item,empty,form,guest-cta}
│   └── admin/    sidebar, topbar, page-header, filters, delete-modal
├── components/
│   ├── nt/            composants anonymes x-nt.* (voir §4)
│   └── status-badge   vue du composant de classe App\View\Components\StatusBadge
├── front/           accueil, pages statiques, modules 1 → 4
├── account/         tableau de bord, mes avis, mes signalements (+ suivi)
├── profile/         formulaires Breeze restylés (rendus dans layouts.account)
├── admin/           back office : dashboard + CRUD des 4 modules + utilisateurs
├── auth/            vues Breeze converties en @extends('layouts.auth')
├── vendor/pagination/nutritrace.blade.php   pagination par défaut (AppServiceProvider)
└── errors/          403, 404, 419, 500, 503
```

### Chaîne d'héritage

```
                         layouts.master
          ┌────────────────────┼────────────────────┐
     layouts.front        layouts.admin         layouts.auth
          │                    │                    │
   layouts.account      admin/* (dashboard,   auth/* (login, register,
          │              CRUD des modules)     forgot/reset, verify, confirm)
   account/*, profile/edit
   front/* (accueil, modules, pages)          errors/* → master directement
```

### Routes

| Fichier | Préfixe / nom | Contenu |
|---|---|---|
| `routes/front.php` | `front.*`, `account.*` (`/mon-espace`, middleware `auth`) | site public, modules 1-4, espace consommateur |
| `routes/admin.php` | `/admin`, `admin.*`, middleware `['auth', 'admin']` | back office (ressources REST) |
| `routes/auth.php` | Breeze (inchangé) | connexion, inscription, mot de passe, vérification |
| `routes/web.php` | `dashboard`, `profile.*` | `dashboard` redirige vers `admin.dashboard` ou `account.dashboard` selon le rôle |

URL en français (`/produits`, `/tracabilite/lots/{code}`, `/admin/signalements`…), noms de routes en anglais. `php artisan route:list --except-vendor` liste les 108 routes.

## 2. Directives Blade utilisées

| Directive | Où (exemples) |
|---|---|
| `@extends` | toutes les pages ; `layouts/front`, `layouts/admin`, `layouts/auth` étendent `layouts/master`, `layouts/account` étend `layouts/front` |
| `@section … @endsection` | toutes les pages (`content`, `hero`, `body`, `account_content`, `page_actions`…) |
| `@section('x', 'valeur')` | `@section('title', 'Produits tracés')` — `front/products/index`, toutes les pages |
| `@yield` avec défaut | `layouts/master` (`@yield('body_class', '…')`), `partials/shared/head` (`@yield('title', 'Accueil')`, `@yield('meta_description', '…')`), `partials/admin/page-header` |
| `@section … @show` | `layouts/front` → bloc `pre_footer` (newsletter par défaut) |
| `@parent` | `front/home` réutilise la newsletter du layout après le CTA producteur et la FAQ ; `layouts/account` vide le bloc |
| `@hasSection` | `layouts/account`, `layouts/admin`, `layouts/auth`, `partials/admin/page-header` |
| `@include` + tableau | `@include('admin.products._form', ['product' => $product])`, `partials/shared/breadcrumb`, `partials/admin/filters` |
| `@includeWhen` / `@includeUnless` | `layouts/front` (flash si session), `partials/front/reviews/block` (formulaire d'avis si connecté, CTA sinon) |
| `@each` + vue vide | `partials/front/reviews/block` : `@each('partials.front.reviews.item', $reviews, 'review', 'partials.front.reviews.empty')` |
| `@push` / `@stack` | `layouts/master` (`@stack('styles')`, `@stack('scripts')`) ; `front/home` pousse un JSON-LD, `front/traceability/show` pousse une feuille d'impression |
| `@component` / `@slot` | `partials/shared/flash` → `@component('components.nt.alert', [...])` + `@slot('title')` |
| Composants anonymes + `@props` | `components/nt/*` (34 composants) |
| Slots nommés `<x-slot:…>` | `x-nt.card` (header/footer), `x-nt.modal` (footer), `x-nt.dropdown` (trigger), `x-nt.page-hero` (aside), `x-nt.data-table` (toolbar/footer), `x-nt.review-card` (actions) |
| Composant de classe | `App\View\Components\StatusBadge` → `<x-status-badge type="report" :value="$report->status" />` |
| `@forelse` / `@empty` | catalogue, listes admin, avis, signalements (19 vues) |
| `@auth` / `@guest` / rôle | `partials/front/navbar`, `partials/front/mobile-menu` (`auth()->user()->isAdmin()`) |
| `@csrf`, `@method` | tous les formulaires (`PUT`, `PATCH`, `DELETE` dans l'admin et l'espace compte) |
| `@error` | `components/nt/form/*` (automatique), `front/reports/create`, `admin/users/edit`… |
| `@class` | `layouts/account`, `partials/front/navbar`, `components/nt/eco-score`… (28 vues) |
| `@checked` / `@selected` / `@disabled` | `partials/front/product/filters`, `components/nt/form/select`, `admin/users/edit`, `admin/batches/show` |

## 3. Système de design — `resources/css/nutritrace.css`

Toutes les couleurs sont des canaux **HSL** définis une seule fois dans `:root` ; aucune valeur hex/rgb ni classe de la palette Tailwind par défaut (désactivée par `--color-*: initial`). Usage : `hsl(var(--nt-primary))` ou `hsl(var(--nt-primary) / .12)` ; côté Tailwind : `bg-primary`, `text-muted-foreground`, `bg-danger/10`…

| Token | HSL | Usage |
|---|---|---|
| `--nt-primary` | `123 46% 34%` | vert nature : actions, liens, succès, éco-score A |
| `--nt-primary-hover` / `-strong` | `123 46% 27%` / `26%` | survol ; texte vert sur fond teinté (AA) |
| `--nt-primary-light` | `123 38% 57%` | accents, logo sur fond sombre |
| `--nt-gold` / `-strong` / `-foreground` | `43 96% 58%` / `36 90% 28%` / `14 30% 16%` | certifications, étoiles, CTA secondaires |
| `--nt-earth` / `-muted` / `-foreground` | `14 26% 29%` / `30 25% 85%` / `0 0% 100%` | terre / ferme : footer, sidebar admin, section greenwashing |
| `--nt-danger` / `-strong` | `0 65% 51%` / `0 65% 40%` | signalements, greenwashing, suppression |
| `--nt-background` / `--nt-surface` | `0 0% 96%` / `0 0% 100%` | fond de page / cartes |
| `--nt-foreground` / `--nt-muted-foreground` | `14 22% 14%` / `14 8% 40%` | texte principal / secondaire |
| `--nt-muted` / `--nt-border` / `--nt-ring` | `30 12% 92%` / `30 10% 87%` / `123 46% 34%` | fonds neutres, bordures, focus |
| `--nt-warning` / `-strong` | `36 100% 50%` / `28 100% 30%` | en attente, priorité haute |
| `--nt-info` / `-strong` | `207 80% 45%` / `207 80% 34%` | en examen, informations |
| `--nt-eco-a … e` | `123 46% 34%` → `0 65% 51%` | échelle éco-score A → E |

Contraste vérifié (WCAG AA) pour chaque paire texte/fond utilisée : blanc sur primaire 5,0:1, texte sur éco-score B/C/D 5,5–9,6:1, tokens `*-strong` sur fonds teintés 5,3–6,4:1. Les classes de composants (`.nt-btn-*`, `.nt-card`, `.nt-badge-*`, `.nt-input`, `.nt-table`, `.nt-skeleton`, `.nt-pattern`…), les keyframes (`fade-in-up`, `float`, `pulse-ring`, `draw-line`) et la règle `prefers-reduced-motion` sont dans le même fichier.

JavaScript (`resources/js/nutritrace/*`) : composants Alpine (modale avec piège du focus, compteurs, notation étoilée au clavier, dépôt de fichiers, aperçu éco-score, assistant de signalement), graphiques Chart.js dont les couleurs sont lues dans les variables CSS (`getComputedStyle`), QR codes générés en SVG `currentColor`.

## 4. Composants

`x-nt.logo` · `button` · `card` · `badge` · `alert` · `empty-state` · `avatar` · `modal` · `dropdown` / `dropdown-link` · `tabs` / `tab-panel` · `accordion` / `accordion-item` · `section-header` · `page-hero` · `stat-card` · `timeline` / `timeline-item` · `data-table` · `filter-bar` · `eco-score` · `cert-badge` · `rating-stars` (affichage + saisie) · `product-image` · `product-card` · `review-card` · `report-card` · `actor-card` · `form.input` · `form.select` · `form.textarea` · `form.file` · `form.checkbox` — plus `x-status-badge` (classe).

Les composants de formulaire gèrent libellé, astérisque obligatoire, aide, `@error`, `old()` et `aria-describedby`/`aria-invalid`. Les couleurs dépendant d'une valeur (éco-score, statut, catégorie) passent toujours par des tables de correspondance de classes complètes, jamais par concaténation.

## 5. Données : modèles, seeders et factories

Toutes les pages lisent la base via Eloquent (`app/Models`). Le jeu de démo est créé par `php artisan migrate --seed` :

- **Seeders** (`database/seeders`, appelés dans l'ordre par `DatabaseSeeder`) : un seeder par entité, qui recrée le jeu de démo « éditorial » (produits, lots, avis, signalements… aux slugs, codes et références stables : `huile-olive-sfax`, `NT-2026-OLV-0412`, `SIG-2026-0001`). Les seeders retrouvent les enregistrements créés avant eux par slug, e-mail ou code, jamais par id.
- **Factories** (`database/factories`) : une par modèle principal, avec des états utiles (`User::factory()->admin()`, `Actor::factory()->producer()`, `Review::factory()->flagged()`, `Report::factory()->about($product)->open()`…). Les seeders les utilisent pour les enregistrements fixes et pour générer en plus 15 consommateurs, 80 avis et 24 signalements répartis sur 12 mois (courbes du tableau de bord).
- **Valeurs dérivées** : `Impact` calcule `eco_points` / `eco_score` à l'enregistrement (`App\Support\EcoScore`) ; `Report` génère sa référence `SIG-AAAA-NNNN` à la création.

| Module | Modèles | Seeders | Contrôleurs |
|---|---|---|---|
| 1 — Produits & Certifications | `Category`, `Certification`, `Product`, `CertificationVerification` | `CategorySeeder`, `CertificationSeeder`, `ProductSeeder`, `CertificationVerificationSeeder` | `Front\ProductController`, `Front\CertificationController`, `Admin\ProductController`, `Admin\CategoryController`, `Admin\CertificationController`, `Admin\CertificationVerificationController` |
| 2 — Traçabilité | `Actor`, `Batch`, `BatchStep` | `ActorSeeder`, `BatchSeeder` | `Front\TraceabilityController`, `Front\ActorController`, `Admin\ActorController`, `Admin\BatchController`, `Admin\BatchStepController` |
| 3 — Empreinte | `Impact`, `EmissionFactor` | `ImpactSeeder`, `EmissionFactorSeeder` | `Front\ImpactController`, `Admin\ImpactController` (formule partagée : `App\Support\EcoScore`) |
| 4 — Signalements & Avis | `Review` (+ `ReviewEvent`), `Report` (+ `ReportEvidence`, `ReportMessage`, `ReportNote`, `ReportEvent`) | `ReviewSeeder`, `ReportSeeder` | `Front\ReviewController`, `Front\ReportController`, `Front\ObservatoryController`, `Account\*`, `Admin\ReviewController`, `Admin\ReportController` |
| Pages publiques | `Faq`, `Testimonial` | `SiteContentSeeder` | `Front\HomeController`, `Front\PageController` |

Ce qui reste à faire dans chaque module est marqué `// TODO(Gestion N)` : les actions d'écriture (création, modification, suppression, modération) valident déjà les données et redirigent, mais n'enregistrent encore rien.

1. Gardez les noms de variables passés aux vues et les attributs lus par les vues (`$product->eco_score`, `$product->rating_avg`, `$batch->total_km`, `$report->target`, `$review->reply`… sont des accesseurs des modèles).
2. Chargez les relations affichées avec `with()` / les scopes fournis (`Product::forCards()`, `Product::withRating()`, `Actor::withStats()`, `Report::withTarget()`) pour éviter les requêtes N+1.
3. Pour la pagination, utilisez `->paginate(n)->withQueryString()` : la vue `vendor.pagination.nutritrace` est déjà la vue par défaut.
4. Les statuts doivent garder les valeurs de `StatusBadge::MAP` (`pending`, `in_review`, `confirmed`…) ; libellés et couleurs restent centralisés.
5. Les compteurs de la sidebar admin viennent de `App\View\Composers\AdminSidebarComposer`, les notifications de la barre du haut de `AdminNotificationsComposer`.
6. La cible d'un signalement est polymorphe (`reportable_type` = `product` \| `actor` \| `certification`, voir `Relation::morphMap` dans `AppServiceProvider`).

| Module | Contrôleurs |
|---|---|
| 1 — Produits & Certifications | `Front\ProductController`, `Front\CertificationController`, `Admin\ProductController`, `Admin\CategoryController`, `Admin\CertificationController`, `Admin\CertificationVerificationController` |
| 2 — Traçabilité | `Front\TraceabilityController`, `Front\ActorController`, `Admin\ActorController`, `Admin\BatchController`, `Admin\BatchStepController` |
| 3 — Empreinte | `Front\ImpactController`, `Admin\ImpactController` (formule partagée : `App\Support\EcoScore`) |
| 4 — Signalements & Avis | `Front\ReviewController`, `Front\ReportController`, `Front\ObservatoryController`, `Account\*`, `Admin\ReviewController`, `Admin\ReportController` |

**Modèle partagé `User`** : une colonne `role` (`admin` \| `actor` \| `consumer`, défaut `consumer`, migration `2026_10_03_000000_add_role_to_users_table`) et la méthode `isAdmin()`, une colonne `last_login_at` (mise à jour à la connexion) et les relations `reviews()`, `reports()` (signalements envoyés) et `assignedReports()`. Le middleware `admin` (`EnsureUserIsAdmin`) est déclaré dans `bootstrap/app.php`.

## 6. Critères d'évaluation → fichiers

| Critère | Où le constater |
|---|---|
| **Front Office intégré** (0,5) | `layouts/front` + `front/home` (hero animé, compteurs, piliers, éco-score, anti-greenwashing, FAQ), `front/*` (catalogue filtrable, fiche produit à onglets, traçabilité de lot avec QR, comparateur, observatoire, assistant de signalement), `account/*` ; responsive 375 / 768 / 1280 px |
| **Back Office intégré** (0,5) | `layouts/admin` (sidebar repliable, topbar, notifications), `admin/dashboard` (6 KPI, 4 graphiques, alertes, modération rapide), CRUD complets des 4 modules, kanban des signalements, file de vérification, éditeur de chronologie |
| **Héritage Blade** (1,5) | 5 layouts sur 3 niveaux (§1), `@section/@yield/@show/@parent` (§2), partials `@include/@includeWhen/@each`, 34 composants anonymes avec `@props` et slots nommés, `@component/@slot`, composant de classe `StatusBadge`, formulaires create/edit partagés `_form` |
| **Personnalisation du thème** (1,5) | `resources/css/nutritrace.css` (tokens HSL, palette par défaut désactivée), logo SVG + favicon, polices Poppins/Inter, contenus 100 % NutriTrace en français, illustrations SVG originales, pages d'erreur thématiques, vues Breeze restylées |

Vérifications automatiques : `php artisan test` (pages publiques 200, pages protégées → `/login`, admin 200 pour un administrateur et 403 pour un consommateur, validation des formulaires, pages d'erreur).
