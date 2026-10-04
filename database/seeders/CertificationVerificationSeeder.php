<?php

namespace Database\Seeders;

use App\Models\Actor;
use App\Models\Certification;
use App\Models\CertificationVerification;
use Illuminate\Database\Seeder;

class CertificationVerificationSeeder extends Seeder
{
    public function run(): void
    {
        $actors = Actor::pluck('id', 'slug');
        $certifications = Certification::pluck('id', 'slug');

        // actor, certification, status, days ago, document
        $verifications = [
            ['souk-ennour', 'bio', 'pending', 1, 'certificat-bio-souk-ennour.pdf'],
            ['ferme-ennahl', 'local', 'pending', 3, 'attestation-circuit-court.pdf'],
            ['conserverie-cap-bon', 'sans-pesticides', 'pending', 4, 'analyse-pesticides-lot-0058.pdf'],
            ['oasis-nakhla', 'equitable', 'approved', 12, 'contrat-equitable-2026.pdf'],
            ['epicerie-verte', 'bio', 'rejected', 20, 'certificat-expire-2024.pdf'],
            ['bergerie-djebel', 'origine-protegee', 'pending', 6, 'cahier-des-charges-zaghouan.pdf'],
        ];

        foreach ($verifications as $i => [$actor, $certification, $status, $daysAgo, $document]) {
            CertificationVerification::factory()->create([
                'actor_id' => $actors[$actor],
                'certification_id' => $certifications[$certification],
                'status' => $status,
                'document' => $document,
                'certificate_number' => 'CERT-'.(20260 + $i + 1).'-'.str_pad((string) ($actors[$actor] * 13), 4, '0', STR_PAD_LEFT),
                'expires_at' => now()->addMonths(6 + ($i + 1) * 2),
                'rejection_reason' => $status === 'rejected' ? 'Certificat expiré depuis 2024.' : null,
                'submitted_at' => now()->subDays($daysAgo),
            ]);
        }
    }
}
