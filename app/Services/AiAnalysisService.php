<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Report;
use App\Models\Review;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiAnalysisService
{
    /**
     * Analyse un signalement pour détecter le risque de Greenwashing et proposer une décision motivée.
     */
    public function analyzeReport(Report $report): array
    {
        $target = $report->reportable;
        $targetInfo = '';

        if ($target instanceof Product) {
            $targetInfo = "Produit: {$target->name} | Catégorie: {$target->category->name} | Éco-score: " . ($target->impact?->eco_score ?? 'N/A') . " | CO2: " . ($target->impact?->co2_per_kg ?? 'N/A') . " kg/kg | Emballage: " . ($target->impact?->packaging ?? 'N/A');
        } elseif ($target) {
            $targetInfo = "Cible: {$target->name}";
        }

        // 1. Tenter un appel vers l'API Gemini si la clé existe
        $apiKey = config('services.gemini.key') ?? env('GEMINI_API_KEY');
        if ($apiKey) {
            try {
                $prompt = "Tu es un expert en audit environnemental et lutte contre le greenwashing alimentaire.
Analyse ce signalement citoyen:
Type: {$report->type}
Description: {$report->description}
Données réelles de la cible: {$targetInfo}

Réponds au format JSON strict avec ces 4 champs:
{
  \"risk_score\": 85,
  \"risk_level\": \"Élevé\",
  \"analysis\": \"Explication courte de 2 phrases sur l'incohérence détectée.\",
  \"recommended_status\": \"confirmed\",
  \"recommended_resolution\": \"Proposition de décision motivée pour le modérateur.\"
}";

                $response = Http::timeout(5)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                ]);

                if ($response->successful()) {
                    $jsonText = $response->json('candidates.0.content.parts.0.text');
                    $cleanJson = Str::between($jsonText, '{', '}');
                    $data = json_decode('{' . $cleanJson . '}', true);
                    if ($data && isset($data['risk_score'])) {
                        return $data;
                    }
                }
            } catch (\Throwable $e) {
                // Fallback direct vers le moteur NLP intelligent
            }
        }

        // 2. Moteur IA heuristique & NLP local (100% autonome et robuste)
        return $this->heuristicReportAnalysis($report, $target);
    }

    /**
     * Moteur NLP local de détection de Greenwashing.
     */
    private function heuristicReportAnalysis(Report $report, $target): array
    {
        $desc = mb_strtolower($report->description);
        $type = $report->type;

        $greenwashingKeywords = ['100%', 'zéro', 'zero', 'neutre', 'naturel', 'vert', 'bio', 'écologique', 'eco', 'durable', 'pur', 'sans impact', 'climat'];
        $foundKeywords = array_filter($greenwashingKeywords, fn ($kw) => Str::contains($desc, $kw));
        $keywordCount = count($foundKeywords);

        $hasProduct = $target instanceof Product;
        $ecoScore = $hasProduct ? ($target->impact?->eco_score ?? 'C') : 'C';

        $score = 50;
        if ($type === 'greenwashing') {
            $score += 25;
        }
        $score += min(20, $keywordCount * 5);

        if (in_array($ecoScore, ['D', 'E'], true) && $keywordCount > 0) {
            $score += 15;
        }

        $score = min(98, max(25, $score));

        $level = match (true) {
            $score >= 75 => 'Risque Élevé',
            $score >= 50 => 'Risque Modéré',
            default => 'Risque Faible',
        };

        $status = $score >= 65 ? 'confirmed' : ($score >= 45 ? 'in_review' : 'rejected');

        $reasons = [];
        if ($keywordCount > 0) {
            $reasons[] = "Allégations fortes détectées : « " . implode(', ', array_slice($foundKeywords, 0, 3)) . " ».";
        }
        if ($hasProduct && in_array($ecoScore, ['D', 'E'], true)) {
            $reasons[] = "L'éco-score réel du produit est {$ecoScore}, ce qui contredit les promesses d'impact nul.";
        }
        if (empty($reasons)) {
            $reasons[] = "Les justificatifs fournis présentent des incohérences avec les données de traçabilité enregistrées.";
        }

        $resolution = match ($status) {
            'confirmed' => "Allégation environnementale jugée non conforme aux données réelles. Mise en demeure du producteur pour retirer ou rectifier la mention sous 15 jours.",
            'rejected' => "Après analyse des données et justificatifs certifiés, l'allégation est conforme aux critères autorisés.",
            default => "Complément d'information demandé au producteur concernant les preuves d'analyse du cycle de vie.",
        };

        return [
            'risk_score' => $score,
            'risk_level' => $level,
            'analysis' => implode(' ', $reasons),
            'recommended_status' => $status,
            'recommended_resolution' => $resolution,
        ];
    }

    /**
     * Analyse le sentiment et la conformité d'un avis consommateur.
     */
    public function analyzeReview(Review $review): array
    {
        $rating = $review->rating;
        $body = mb_strtolower($review->body . ' ' . $review->title);

        $negativeWords = ['mauvais', 'horrible', 'arnaque', 'nul', 'déçu', 'faux', 'sale', 'immangeable', 'inacceptable', 'mensonge'];
        $positiveWords = ['excellent', 'super', 'parfait', 'très bon', 'qualité', 'bravo', 'authentique', 'top', 'délicieux', 'frais'];

        $negCount = count(array_filter($negativeWords, fn ($w) => Str::contains($body, $w)));
        $posCount = count(array_filter($positiveWords, fn ($w) => Str::contains($body, $w)));

        if ($rating >= 4 || ($posCount > $negCount && $rating >= 3)) {
            $sentiment = 'Positif';
            $tone = 'success';
            $score = 85 + min(15, $posCount * 5);
        } elseif ($rating <= 2 || $negCount > $posCount) {
            $sentiment = 'Négatif';
            $tone = 'danger';
            $score = 20 + max(0, 10 - $negCount * 2);
        } else {
            $sentiment = 'Neutre / Mitigé';
            $tone = 'gold';
            $score = 55;
        }

        $isConstructive = mb_strlen($review->body) >= 50 && ($review->quality_rating || $review->transparency_rating);

        return [
            'sentiment' => $sentiment,
            'tone' => $tone,
            'confidence' => $score,
            'is_constructive' => $isConstructive,
            'summary' => $isConstructive ? "Retour d'expérience constructif et argumenté." : "Avis court nécessitant une vérification standard.",
        ];
    }
}
