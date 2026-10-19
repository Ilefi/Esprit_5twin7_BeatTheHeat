<?php

namespace App\Services;

use DateTimeInterface;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\StrayRequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Read-only client for the Open Food Facts API (open data, no key).
 * Every answer is cached; a failed or empty call returns null so pages simply hide the block.
 */
class OpenFoodFacts
{
    public const GRADES = ['A', 'B', 'C', 'D', 'E'];

    public const NOVA_LABELS = [
        1 => 'Aliment pas ou peu transformé',
        2 => 'Ingrédient culinaire transformé',
        3 => 'Aliment transformé',
        4 => 'Aliment ultra-transformé',
    ];

    /** Nutrients shown per 100 g: OFF field => [label, unit]. */
    public const NUTRIENTS = [
        'energy-kcal_100g' => ['Énergie', 'kcal'],
        'fat_100g' => ['Matières grasses', 'g'],
        'saturated-fat_100g' => ['dont acides gras saturés', 'g'],
        'sugars_100g' => ['Sucres', 'g'],
        'fiber_100g' => ['Fibres', 'g'],
        'proteins_100g' => ['Protéines', 'g'],
        'salt_100g' => ['Sel', 'g'],
    ];

    /** The 14 regulated EU allergens, as OFF tags. */
    public const ALLERGENS = [
        'gluten' => 'Gluten', 'crustaceans' => 'Crustacés', 'eggs' => 'Œufs', 'fish' => 'Poisson',
        'peanuts' => 'Arachides', 'soybeans' => 'Soja', 'milk' => 'Lait', 'nuts' => 'Fruits à coque',
        'celery' => 'Céleri', 'mustard' => 'Moutarde', 'sesame-seeds' => 'Sésame',
        'sulphur-dioxide-and-sulphites' => 'Sulfites', 'lupin' => 'Lupin', 'molluscs' => 'Mollusques',
    ];

    private const PRODUCT_FIELDS = 'code,product_name,product_name_fr,generic_name_fr,generic_name,brands,quantity,ingredients_text_fr,ingredients_text,'
        .'nutriscore_grade,nova_group,ecoscore_grade,environmental_score_grade,allergens_tags,categories_tags,nutriments';

    /**
     * A product by barcode, normalized for the views and the admin import.
     *
     * @return array{barcode: string, name: ?string, description: ?string, brands: ?string, quantity: ?string, ingredients: ?string, nutriscore: ?string, nova: ?int, green_score: ?string, allergens: list<string>, categories: list<string>, nutrients: array<string, array{label: string, value: float, unit: string}>, url: string}|null
     */
    public function product(string $barcode): ?array
    {
        if (! preg_match('/^\d{8,14}$/', $barcode)) {
            return null;
        }

        return $this->remember("off.product.{$barcode}", now()->addDay(), function () use ($barcode) {
            $response = $this->client()->get("/api/v2/product/{$barcode}.json", ['fields' => self::PRODUCT_FIELDS]);

            $product = $response->successful() && $response->json('status') === 1 ? $response->json('product') : null;

            return $product ? $this->normalize($barcode, $product) : null;
        });
    }

    /**
     * Exact Nutri-Score and Green-Score distributions of the OFF products in a taxonomy category (e.g. en:honeys).
     *
     * @return array{total: int, nutriscore: array<string, int>, green_score: array<string, int>, nutriscore_avg: ?string, green_score_avg: ?string, url: string}|null
     */
    public function categoryBenchmark(string $tag): ?array
    {
        if (! preg_match('/^[a-z]{2}:[a-z0-9-]+$/', $tag)) {
            return null;
        }

        return $this->remember("off.category.{$tag}", now()->addWeek(), function () use ($tag) {
            // Search-a-licious (search.openfoodfacts.org) returns exact facet counts in one call.
            $response = $this->client(config('services.openfoodfacts.search_url'))->get('/search', [
                'q' => 'categories_tags:"'.$tag.'"',
                'facets' => 'nutrition_grades,ecoscore_grade',
                'fields' => 'code',
                'page_size' => 1,
            ]);

            $facets = $response->successful() ? $response->json('facets', []) : [];
            $nutriscore = $this->distribution($facets['nutrition_grades']['items'] ?? []);
            $greenScore = $this->distribution($facets['ecoscore_grade']['items'] ?? []);
            $total = (int) collect($facets['nutrition_grades']['items'] ?? [])->sum('count');

            if (! $total) {
                return null;
            }

            return [
                'total' => $total,
                'nutriscore' => $nutriscore,
                'green_score' => $greenScore,
                'nutriscore_avg' => $this->averageGrade($nutriscore),
                'green_score_avg' => $this->averageGrade($greenScore),
                'url' => $this->baseUrl().'/category/'.Str::after($tag, ':'),
            ];
        });
    }

    private function client(?string $url = null): PendingRequest
    {
        return Http::baseUrl(rtrim($url ?? $this->baseUrl(), '/'))
            ->withUserAgent(config('services.openfoodfacts.user_agent'))
            ->timeout(config('services.openfoodfacts.timeout'))
            ->acceptJson();
    }

    private function baseUrl(): string
    {
        return rtrim(config('services.openfoodfacts.url'), '/');
    }

    /** Caches successes for $ttl and failures (stored as false) for 10 minutes, so a down API is not hit on every page. */
    private function remember(string $key, DateTimeInterface $ttl, callable $fetch): ?array
    {
        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached ?: null;
        }

        try {
            $value = $fetch();
        } catch (HttpClientException|StrayRequestException) {
            // API down, timeout, or a test without Http::fake(): expected, the block is hidden.
            $value = null;
        } catch (Throwable $e) {
            report($e);
            $value = null;
        }

        Cache::put($key, $value ?? false, $value ? $ttl : now()->addMinutes(10));

        return $value;
    }

    private function normalize(string $barcode, array $p): array
    {
        $nutrients = [];
        foreach (self::NUTRIENTS as $field => [$label, $unit]) {
            if (is_numeric($p['nutriments'][$field] ?? null)) {
                $nutrients[$field] = ['label' => $label, 'value' => round((float) $p['nutriments'][$field], 1), 'unit' => $unit];
            }
        }

        $allergens = collect($p['allergens_tags'] ?? [])
            ->map(fn (string $tag) => self::ALLERGENS[Str::after($tag, ':')] ?? null)
            ->filter()->unique()->values()->all();

        $nova = (int) ($p['nova_group'] ?? 0);

        return [
            'barcode' => $barcode,
            'name' => $this->text($p['product_name_fr'] ?? null) ?? $this->text($p['product_name'] ?? null),
            'description' => $this->text($p['generic_name_fr'] ?? null) ?? $this->text($p['generic_name'] ?? null),
            'brands' => $this->text($p['brands'] ?? null),
            'quantity' => $this->text($p['quantity'] ?? null),
            'ingredients' => $this->text($p['ingredients_text_fr'] ?? null) ?? $this->text($p['ingredients_text'] ?? null),
            'nutriscore' => $this->grade($p['nutriscore_grade'] ?? null),
            'nova' => isset(self::NOVA_LABELS[$nova]) ? $nova : null,
            'green_score' => $this->grade($p['environmental_score_grade'] ?? $p['ecoscore_grade'] ?? null),
            'allergens' => $allergens,
            'categories' => array_values(array_filter($p['categories_tags'] ?? [], 'is_string')),
            'nutrients' => $nutrients,
            'url' => $this->baseUrl().'/product/'.$barcode,
        ];
    }

    private function text(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return $value === '' ? null : $value;
    }

    /** "a".."e" → "A".."E" (Green-Score "a-plus" counts as A, "f" as E); "unknown" and "not-applicable" → null. */
    private function grade(mixed $value): ?string
    {
        $value = match ($value = strtoupper((string) $value)) {
            'A-PLUS' => 'A',
            'F' => 'E',
            default => $value,
        };

        return in_array($value, self::GRADES, true) ? $value : null;
    }

    /**
     * @param  list<array{key: string, count: int}>  $items  facet items
     * @return array<string, int>
     */
    private function distribution(array $items): array
    {
        $counts = array_fill_keys(self::GRADES, 0);
        foreach ($items as $item) {
            if ($grade = $this->grade($item['key'] ?? null)) {
                $counts[$grade] += (int) ($item['count'] ?? 0);
            }
        }

        return $counts;
    }

    /** Mean grade of a distribution (A = 1 … E = 5), rounded to the nearest letter. */
    private function averageGrade(array $distribution): ?string
    {
        $total = array_sum($distribution);
        if (! $total) {
            return null;
        }

        $weighted = 0;
        foreach (self::GRADES as $i => $grade) {
            $weighted += ($i + 1) * $distribution[$grade];
        }

        return self::GRADES[(int) round($weighted / $total) - 1];
    }
}
