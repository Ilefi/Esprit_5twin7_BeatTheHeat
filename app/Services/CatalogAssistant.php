<?php

namespace App\Services;

use App\Http\Controllers\Admin\CategoryController;
use App\Models\Category;
use App\Models\Certification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * AI writing help for the Module 1 admin forms (Gemini): product sheets and category settings.
 * Gemini only drafts: the output is checked here, and the admin reviews it before saving.
 */
class CatalogAssistant
{
    /** Environmental claims that need proof: flagged in the generated text so the admin checks them (anti-greenwashing). */
    public const UNVERIFIED_CLAIMS = [
        'zéro impact', 'zero impact', 'neutre en carbone', 'neutre pour le climat', 'sans impact', '100 % naturel', '100% naturel',
        'écologique', 'respectueux de l\'environnement', 'éco-responsable', 'durable',
    ];

    public const NOT_CONFIGURED = 'L\'IA n\'est pas activée : ajoutez GEMINI_API_KEY dans le fichier .env (clé gratuite sur aistudio.google.com).';

    public const UNAVAILABLE = 'Gemini n\'a pas pu répondre pour le moment, réessayez dans un instant.';

    public function __construct(private Gemini $gemini, private OpenFoodFacts $openFoodFacts) {}

    public function configured(): bool
    {
        return $this->gemini->configured();
    }

    /**
     * Description and composition of a product, written from what the admin typed (and Open Food Facts when a barcode is set).
     *
     * @param  array{name: string, region?: ?string, format?: ?string, composition?: ?string, barcode?: ?string}  $input
     * @param  Collection<int, Certification>  $certifications
     * @return array{description: string, composition: ?string, warnings: list<string>}|null
     */
    public function productSheet(array $input, ?Category $category, Collection $certifications): ?array
    {
        $off = filled($input['barcode'] ?? null) ? $this->openFoodFacts->product($input['barcode']) : null;

        $facts = array_filter([
            'Nom' => $input['name'],
            'Catégorie' => $category?->name,
            'Région d\'origine (Tunisie)' => $input['region'] ?? null,
            'Format' => $input['format'] ?? null,
            'Composition saisie' => $input['composition'] ?? null,
            'Certifications valides' => $certifications->pluck('name')->implode(', '),
            'Marque (Open Food Facts)' => $off['brands'] ?? null,
            'Ingrédients (Open Food Facts)' => $off['ingredients'] ?? null,
            'Allergènes (Open Food Facts)' => implode(', ', $off['allergens'] ?? []),
        ], 'filled');

        $data = $this->gemini->json(
            'Tu es le rédacteur de NutriTrace, une plateforme tunisienne de traçabilité alimentaire "de la ferme à la fourchette". '
            .'Tu écris en français, sur un ton factuel et chaleureux, sans superlatifs ni majuscules inutiles. '
            .'Règles : n\'invente aucun chiffre, label ou certification ; ne cite une certification (bio, AOP…) que si elle figure dans les données ; '
            .'n\'utilise jamais d\'allégation environnementale non prouvée (zéro impact, neutre en carbone, écologique, durable…).',
            "Rédige la fiche de ce produit à partir de ces données :\n".$this->lines($facts)."\n\n"
            .'- description : 2 à 4 phrases (300 à 700 caractères) : le produit, son origine, son goût ou son usage, son conditionnement.'."\n"
            .'- composition : la liste des ingrédients si elle est évidente (produit brut ou données fournies), sinon une chaîne vide.',
            [
                'type' => 'OBJECT',
                'properties' => [
                    'description' => ['type' => 'STRING'],
                    'composition' => ['type' => 'STRING'],
                ],
                'required' => ['description', 'composition'],
            ],
        );

        $description = $this->clean($data['description'] ?? null, 2000);
        if (! $description || mb_strlen($description) < 20) {
            return null;
        }

        return [
            'description' => $description,
            'composition' => $this->clean($data['composition'] ?? null, 1000),
            'warnings' => $this->unverifiedClaims($description),
        ];
    }

    /**
     * Icon, Open Food Facts tag and substitute categories for a category name.
     * The tag is checked against Open Food Facts; substitutes can only be existing categories.
     *
     * @return array{icon: ?string, off_tag: ?string, off_products: ?int, substitutes: list<array{id: int, name: string, reason: string}>, summary: ?string}|null
     */
    public function categorySettings(string $name, ?Category $current): ?array
    {
        $others = Category::with('products:id,category_id,name')
            ->when($current, fn ($query) => $query->whereKeyNot($current->id))
            ->orderBy('id')
            ->get();

        $catalog = $others->map(fn (Category $category) => "- id {$category->id} : {$category->name}"
            .' (exemples : '.($category->products->take(4)->pluck('name')->implode(', ') ?: 'aucun produit').')')->implode("\n");
        $products = $current?->products()->orderBy('id')->take(8)->pluck('name')->implode(', ');

        $data = $this->gemini->json(
            'Tu aides l\'administrateur de NutriTrace, une plateforme tunisienne de traçabilité alimentaire, à configurer ses catégories de produits. Tu réponds en français.',
            "Catégorie à configurer : « {$name} »".($products ? " (produits actuels : {$products})" : '')."\n\n"
            ."Catégories existantes :\n{$catalog}\n\n"
            .'- icon : l\'icône Font Awesome la plus adaptée parmi la liste imposée.'."\n"
            .'- off_tag : le tag de la taxonomie Open Food Facts en anglais (forme « en:honeys », « en:dried-fruits ») qui correspond le mieux.'."\n"
            .'- substitutes : 0 à 3 catégories existantes (par id) dont les produits peuvent remplacer ceux-ci pour le même usage ou le même moment de consommation, avec une raison courte (une phrase). Liste vide si aucune ne convient.'."\n"
            .'- summary : une phrase qui résume tes choix.',
            [
                'type' => 'OBJECT',
                'properties' => [
                    'icon' => ['type' => 'STRING', 'enum' => array_keys(CategoryController::ICONS)],
                    'off_tag' => ['type' => 'STRING'],
                    'substitutes' => [
                        'type' => 'ARRAY',
                        'items' => [
                            'type' => 'OBJECT',
                            'properties' => ['id' => ['type' => 'INTEGER'], 'reason' => ['type' => 'STRING']],
                            'required' => ['id', 'reason'],
                        ],
                    ],
                    'summary' => ['type' => 'STRING'],
                ],
                'required' => ['icon', 'off_tag', 'substitutes', 'summary'],
            ],
        );

        if (! $data) {
            return null;
        }

        $tag = Str::lower(trim((string) ($data['off_tag'] ?? '')));
        $tag = preg_match('/^[a-z]{2}:[a-z0-9-]+$/', $tag) ? $tag : null;
        $known = $others->keyBy('id');

        return [
            'icon' => array_key_exists($data['icon'] ?? '', CategoryController::ICONS) ? $data['icon'] : null,
            'off_tag' => $tag,
            // Null when Open Food Facts doesn't know the tag (or is down): the admin sees it is unverified.
            'off_products' => $tag ? ($this->openFoodFacts->categoryBenchmark($tag)['total'] ?? null) : null,
            'substitutes' => collect($data['substitutes'] ?? [])
                ->filter(fn ($item) => is_array($item) && $known->has((int) ($item['id'] ?? 0)))
                ->unique(fn (array $item) => (int) $item['id'])
                ->take(3)
                ->map(fn (array $item) => [
                    'id' => (int) $item['id'],
                    'name' => $known[(int) $item['id']]->name,
                    'reason' => $this->clean($item['reason'] ?? null, 200) ?? '',
                ])
                ->values()->all(),
            'summary' => $this->clean($data['summary'] ?? null, 300),
        ];
    }

    /** @param  array<string, string>  $facts */
    private function lines(array $facts): string
    {
        return collect($facts)->map(fn (string $value, string $label) => "- {$label} : {$value}")->implode("\n");
    }

    private function clean(mixed $value, int $max): ?string
    {
        $value = is_string($value) ? trim(preg_replace('/\s+/u', ' ', $value)) : '';

        return $value === '' ? null : Str::limit($value, $max - 1, '…');
    }

    /** @return list<string> */
    private function unverifiedClaims(string $text): array
    {
        $text = mb_strtolower($text);

        return array_values(array_filter(self::UNVERIFIED_CLAIMS, fn (string $claim) => str_contains($text, $claim)));
    }
}
