<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Single source of truth for every status / priority / type label and color in the UI.
 *
 * Usage: <x-status-badge type="report" :value="$report->status" />
 * Labels for selects and filters: StatusBadge::options('report').
 */
class StatusBadge extends Component
{
    /** Full class names only, so Tailwind/CSS never depends on string concatenation. */
    private const VARIANTS = [
        'primary' => 'nt-badge nt-badge-primary',
        'success' => 'nt-badge nt-badge-success',
        'warning' => 'nt-badge nt-badge-warning',
        'danger' => 'nt-badge nt-badge-danger',
        'info' => 'nt-badge nt-badge-info',
        'gold' => 'nt-badge nt-badge-gold',
        'earth' => 'nt-badge nt-badge-earth',
        'muted' => 'nt-badge',
    ];

    /** type => value => [label, variant, Font Awesome icon] */
    public const MAP = [
        'report' => [
            'pending' => ['En attente', 'warning', 'fa-hourglass-half'],
            'in_review' => ['En cours d\'examen', 'info', 'fa-magnifying-glass'],
            'confirmed' => ['Fondé', 'danger', 'fa-circle-exclamation'],
            'rejected' => ['Rejeté', 'muted', 'fa-xmark'],
            'resolved' => ['Résolu', 'success', 'fa-check'],
        ],
        'priority' => [
            'low' => ['Basse', 'muted', 'fa-angle-down'],
            'medium' => ['Moyenne', 'info', 'fa-equals'],
            'high' => ['Haute', 'warning', 'fa-angle-up'],
            'critical' => ['Critique', 'danger', 'fa-angles-up'],
        ],
        'report_type' => [
            'greenwashing' => ['Greenwashing', 'danger', 'fa-leaf'],
            'dubious_certification' => ['Certification douteuse', 'gold', 'fa-award'],
            'traceability_error' => ['Traçabilité erronée', 'info', 'fa-route'],
            'misleading_footprint' => ['Empreinte trompeuse', 'warning', 'fa-smog'],
            'health_quality' => ['Problème sanitaire / qualité', 'earth', 'fa-triangle-exclamation'],
            'other' => ['Autre', 'muted', 'fa-ellipsis'],
        ],
        'review' => [
            'pending' => ['En attente', 'warning', 'fa-hourglass-half'],
            'published' => ['Publié', 'success', 'fa-check'],
            'rejected' => ['Rejeté', 'muted', 'fa-xmark'],
            'flagged' => ['Signalé', 'danger', 'fa-flag'],
        ],
        'batch' => [
            'in_production' => ['En production', 'gold', 'fa-industry'],
            'in_transit' => ['En transit', 'info', 'fa-truck'],
            'delivered' => ['Livré', 'success', 'fa-box-open'],
        ],
        'product' => [
            'published' => ['Publié', 'success', 'fa-eye'],
            'pending' => ['À valider', 'warning', 'fa-hourglass-half'],
            'draft' => ['Brouillon', 'muted', 'fa-pen'],
        ],
        'verification' => [
            'pending' => ['À vérifier', 'warning', 'fa-hourglass-half'],
            'approved' => ['Approuvé', 'success', 'fa-circle-check'],
            'rejected' => ['Refusé', 'danger', 'fa-circle-xmark'],
        ],
        'role' => [
            'admin' => ['Administrateur', 'earth', 'fa-user-shield'],
            'actor' => ['Acteur', 'gold', 'fa-tractor'],
            'consumer' => ['Consommateur', 'primary', 'fa-user'],
        ],
        'actor_type' => [
            'producer' => ['Producteur', 'primary', 'fa-tractor'],
            'processor' => ['Transformateur', 'earth', 'fa-industry'],
            'distributor' => ['Distributeur', 'info', 'fa-store'],
        ],
    ];

    public string $label;

    public string $classes;

    public string $iconClass;

    public function __construct(
        public string $type,
        public ?string $value = null,
        public bool $icon = true,
    ) {
        [$label, $variant, $iconClass] = self::MAP[$type][$value] ?? [ucfirst((string) $value), 'muted', 'fa-circle'];

        $this->label = $label;
        $this->classes = self::VARIANTS[$variant];
        $this->iconClass = $iconClass;
    }

    public static function labelFor(string $type, ?string $value): string
    {
        return self::MAP[$type][$value][0] ?? ucfirst((string) $value);
    }

    public static function iconFor(string $type, ?string $value): string
    {
        return self::MAP[$type][$value][2] ?? 'fa-circle';
    }

    /**
     * @return array<string, string> value => label, for selects and filters
     */
    public static function options(string $type): array
    {
        return array_map(fn (array $entry) => $entry[0], self::MAP[$type]);
    }

    public function render(): View
    {
        return view('components.status-badge');
    }
}
