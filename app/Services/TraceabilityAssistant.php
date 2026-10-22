<?php

namespace App\Services;

use App\Http\Controllers\Admin\BatchController;
use App\Models\Actor;
use App\Models\Batch;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * AI writing help for the Module 2 admin step form (Gemini): drafts the "action réalisée" text of a traceability step.
 * Gemini only drafts: the output is cleaned and checked here (unproven claims, invented figures), and the admin reviews it before saving.
 */
class TraceabilityAssistant
{
    public function __construct(private Gemini $gemini) {}

    public function configured(): bool
    {
        return $this->gemini->configured();
    }

    /**
     * Draft of the action carried out at a step, written from the fields the admin already filled in.
     *
     * @param  array{stage: string, title: string, location?: ?string, date?: ?string, distance_km?: int|float|string|null}  $input
     * @return array{action: string, warnings: list<string>}|null
     */
    public function stepAction(Batch $batch, array $input, ?Actor $actor): ?array
    {
        $facts = $this->lines(array_filter([
            'Produit' => $batch->product->name,
            'Lot' => $batch->code,
            'Maillon de la chaîne' => BatchController::STAGES[$input['stage']] ?? null,
            'Intitulé de l\'étape' => $input['title'],
            'Lieu' => $input['location'] ?? null,
            'Acteur responsable' => $actor?->name,
            'Date' => filled($input['date'] ?? null) ? Carbon::parse($input['date'])->format('d/m/Y') : null,
            'Distance depuis l\'étape précédente (km)' => filled($input['distance_km'] ?? null) ? (string) $input['distance_km'] : null,
        ], 'filled'));

        $data = $this->gemini->json(
            'Tu es le rédacteur de NutriTrace, une plateforme tunisienne de traçabilité alimentaire "de la ferme à la fourchette". '
            .'Tu écris en français, sur un ton factuel, sans superlatifs. '
            .'Règles : n\'invente aucun chiffre, quantité, température, label ou certification absents des données ; '
            .'n\'utilise jamais d\'allégation environnementale non prouvée (zéro impact, neutre en carbone, écologique, durable…) ; '
            .'ne cite aucun nom de personne.',
            "Rédige l'action réalisée à cette étape de la chaîne de traçabilité à partir de ces données :\n{$facts}\n\n"
            .'- action : 1 à 2 phrases (80 à 300 caractères) qui décrivent concrètement ce qui a été fait à cette étape, sans répéter l\'intitulé mot pour mot.',
            [
                'type' => 'OBJECT',
                'properties' => ['action' => ['type' => 'STRING']],
                'required' => ['action'],
            ],
        );

        $action = $this->clean($data['action'] ?? null, 500);
        if (! $action || mb_strlen($action) < 20) {
            return null;
        }

        return ['action' => $action, 'warnings' => $this->warnings($action, $facts)];
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

    /**
     * Anti-greenwashing and anti-hallucination checks: unproven environmental claims, and figures the admin never gave.
     *
     * @return list<string>
     */
    private function warnings(string $action, string $facts): array
    {
        $lower = mb_strtolower($action);
        $claims = array_filter(CatalogAssistant::UNVERIFIED_CLAIMS, fn (string $claim) => str_contains($lower, $claim));

        preg_match_all('/\d+(?:[.,]\d+)?/u', $action, $used);
        preg_match_all('/\d+(?:[.,]\d+)?/u', $facts, $given);
        $numbers = array_unique(array_diff($used[0], $given[0]));

        return [
            ...array_map(fn (string $claim) => "Allégation à vérifier : {$claim}", array_values($claims)),
            ...array_map(fn (string $number) => "Chiffre non fourni : {$number}", array_values($numbers)),
        ];
    }
}
