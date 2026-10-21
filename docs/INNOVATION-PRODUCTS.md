# Module 1 innovation — Smart recommendations & Open Food Facts

What was added to **Produits & Catégories**, how each piece works, and how to demo it.

## 1. The features in one table

| | Product | Category |
|---|---|---|
| **Public product page** (`/produits/{slug}`) | "Meilleures alternatives" block, "Nutrition & repères" tab (Nutri-Score, NOVA, nutrients, allergens from Open Food Facts) | "Plus durable que X % des produits de la catégorie", NutriTrace vs world (Open Food Facts) benchmark |
| **Admin** | Barcode field + **Import Open Food Facts** button on the product form | Open Food Facts tag + **substitute categories** in the category modals, eco-score stats on each category card |
| **API** | `GET /api/v2/product/{barcode}` (world.openfoodfacts.org) | `GET /search?facets=…` (search.openfoodfacts.org) |

## 2. Smart recommendation — "Meilleures alternatives"

`app/Services/ProductRecommender.php`

**Which products are candidates?** Published products that:
1. are in the **same category**, or in one of its **substitute categories** (set by the admin, see §4);
2. have a **better eco-score** (more eco points) than the current product. If the current product has no footprint yet, any product with one counts.

**How are they ranked?** A score out of 100 (`ProductRecommender::WEIGHTS`):

| Criterion | Points |
|---|---|
| Eco points (0–100) | 60 |
| Average published rating (0–5) | 20 |
| Valid certifications (up to 3) | 10 |
| Same category (not a substitute) | 6 |
| Same region | 4 |

The top 3 are shown. Each one shows up to 3 **reasons** worked out from the data: "Éco-score A au lieu de D", "−40 % de CO₂", "210 km de transport en moins", "Certifié Bio", "Même région (Sfax)". The **Comparer** button opens the existing Module 3 comparator (`/empreinte/comparer`) with both products, so no comparator was duplicated.

**Category benchmark** (`categoryBenchmark()`): the product's rank among the published products of its category that have a footprint. It's shown as "Plus durable que 72 % des produits « Fruits »" in the product header, with details in the *Nutrition & repères* tab.

## 3. Open Food Facts API

`app/Services/OpenFoodFacts.php`. Open data, **no API key**. Config is in `config/services.php` → `openfoodfacts` (`url`, `search_url`, `user_agent`, `timeout`). Every value has a default, so `.env` needs nothing.

| Method | Endpoint | Used for |
|---|---|---|
| `product($barcode)` | `world.openfoodfacts.org/api/v2/product/{barcode}.json?fields=…` | Admin import + public nutrition tab |
| `categoryBenchmark($tag)` | `search.openfoodfacts.org/search?q=categories_tags:"en:honeys"&facets=nutrition_grades,ecoscore_grade` | World Nutri-Score / Green-Score distributions of a category |

Design choices:
- **Normalized output.** Raw OFF JSON never reaches the views. Grades become `A`–`E` (`a-plus` → A, `f` → E, `unknown` → null). Allergens are mapped to the 14 EU allergens in French. Only numeric nutrients are kept.
- **Cache.** A product is cached 1 day and a category 1 week. A failed call is cached 10 minutes, so a down API isn't hit on every page view.
- **Never breaks a page.** A timeout, an HTTP error or an unknown barcode all return `null`, and the view shows an empty state instead. Unexpected errors (bugs) are still logged with `report()`.
- **Polite client.** It sends a custom `User-Agent`, as Open Food Facts asks.
- **Why search.openfoodfacts.org?** The classic `/api/v2/search` endpoint was answering "temporarily unavailable" during development. The newer search service (Search-a-licious) returns **exact** counts per grade in a single call.

## 4. Category side

- **`categories.off_tag`**: the Open Food Facts taxonomy tag of the category (`en:honeys`, `en:dried-fruits`…). It's used for the world benchmark and to **suggest the category** during a barcode import: if the OFF product's categories include our `off_tag`, that category is pre-selected. It's validated by regex (`en:honeys` format).
- **`category_substitutes` table**: categories whose products can replace each other. The link is **symmetric**: `Category::syncSubstitutes()` writes both directions, so "Fruits secs ↔ Fruits" is set once from either side. A category can't be its own substitute.
- **Admin categories page**: each card shows the average eco-score, the A→E distribution bar (`<x-nt.grade-bar>`), the substitutes and the OFF tag.

Seeded values (`CategorySeeder`): every category has a real OFF tag, and **Fruits ↔ Fruits secs** and **Produits de la ruche ↔ Fruits secs** are substitutes.

## 5. Admin barcode import

On `/admin/produits/create` (or edit), the **Import Open Food Facts** card works like this:
1. Type an EAN (8, 12, 13 or 14 digits) and click **Importer** (or press Enter).
2. The Alpine component `ntBarcodeImport` (`resources/js/nutritrace/components.js`) calls `GET /admin/produits/open-food-facts/{barcode}` (`admin.products.lookup`, admin only), which returns JSON.
3. **Only empty fields are filled** (name, format, description, composition, category), so nothing the admin typed is overwritten. A summary shows the brand, Nutri-Score, NOVA group, allergens and a link to the OFF page.
4. The barcode is saved with the product (`products.barcode`, nullable and unique). Once saved, the public page shows the nutrition tab.

The photo still has to be uploaded: the project doesn't load remote images.

## 6. Files

**New**
- `database/migrations/2026_10_19_000001_add_open_food_facts_to_products_and_categories.php`: `products.barcode`, `categories.off_tag`, `category_substitutes`
- `app/Services/OpenFoodFacts.php`, `app/Services/ProductRecommender.php`
- `resources/views/partials/front/product/alternatives.blade.php`, `…/nutrition.blade.php`
- `resources/views/admin/categories/_smart-fields.blade.php`: OFF tag + substitutes, shared by the create and edit modals
- `resources/views/components/nt/grade-bar.blade.php`, `nutri-score.blade.php`
- `tests/Feature/ProductRecommendationTest.php`: 8 tests

**Changed**
- Models: `Category` (`off_tag`, `substitutes()`, `impacts()`, `syncSubstitutes()`), `Product` (`barcode`)
- Controllers: `Front\ProductController@show`, `Admin\ProductController@lookup`, `Admin\CategoryController` (stats, new rules)
- `ProductRequest` (barcode rule), `routes/admin.php` (lookup route), `config/services.php`, `lang/fr/validation.php`
- Views: product page, admin product form/show, admin categories; `x-nt.form.checkbox` gained `bag` / `use-old` props (for several forms on one page)
- `tests/TestCase.php`: `Http::preventStrayRequests()`, so no test ever calls a real API

## 7. Try it

```
php artisan migrate:fresh --seed   # new columns + seeded OFF tags and substitutes (resets demo data)
npm run build                      # or npm run dev
```

1. **Alternatives**: open `/produits/amandes-sfax` (eco-score D). The block suggests fruits and honey from the substitute categories.
2. **Category benchmark**: open `/produits/huile-olive-sfax` (NutriTrace ranking, 2nd of 3) and `/produits/miel-thym-zaghouan` (world Green-Score / Nutri-Score distributions for `en:honeys`), *Nutrition & repères* tab. The ranking needs at least 2 rated products in the category.
3. **Barcode import**: on `/admin/produits/create`, type `3017620422003` and click **Importer**. The fields fill in from Open Food Facts.
4. **Nutrition tab**: edit a product, set a real barcode, save, and open its public page.
5. **Categories**: on `/admin/categories`, edit a category to change its OFF tag or its substitutes. The alternatives on the product pages change with it.

Tests: `php artisan test --filter=ProductRecommendationTest`. Open Food Facts is faked with `Http::fake()`, including the "API down" case.

Click-by-click demo of every feature, AI included: `docs/DEMO-PRODUCTS.md`.

## 8. AI writing help (Gemini)

`app/Services/Gemini.php` (generic client) and `app/Services/CatalogAssistant.php` (prompts + checks). Config: `config/services.php` → `gemini` (`GEMINI_API_KEY`, free on aistudio.google.com; `GEMINI_MODEL`, default `gemini-3.8-flash`).

| | Product form (`/admin/produits/create`, edit) | Category modals (`/admin/categories`) |
|---|---|---|
| Button | **Rédiger avec l'IA** (`ntAiProductWriter`) | **Suggérer avec l'IA** (`ntAiCategoryAssistant`) |
| Route | `POST /admin/produits/ia/fiche` (`admin.products.ai`) | `POST /admin/categories/ia/suggestions` (`admin.categories.ai`) |
| Gemini gets | name, category, region, format, composition, valid certifications, Open Food Facts data of the barcode | the name, the current products of the category, the other categories with example products |
| Gemini returns | description, composition | icon, Open Food Facts tag, up to 3 substitutes with a reason, a summary |

Design choices:
- **Structured output.** Gemini must answer JSON matching a schema (`responseMimeType` + `responseSchema`), so nothing is parsed out of free text.
- **The server checks everything.** The icon must be in `CategoryController::ICONS`, the tag must match `en:xxx` and is **checked on Open Food Facts** (number of products shown, or "à vérifier"), substitutes must be existing categories (never the category itself), texts are trimmed to the column limits.
- **Anti-greenwashing.** The prompt forbids invented labels and unproven claims; if the text still contains one (`CatalogAssistant::UNVERIFIED_CLAIMS`: "zéro impact", "écologique"…), the admin sees a warning.
- **The admin decides.** Like the barcode import, only empty fields are filled; a button replaces the admin's text on request. Nothing is saved until the form is submitted.
- **Never breaks the form.** No key → 503 with the setup message; Gemini error or timeout → 502 "réessayez" (details logged as a warning, see `php artisan pail`).

Tests: `php artisan test --filter=CatalogAssistantTest` (Gemini faked).
