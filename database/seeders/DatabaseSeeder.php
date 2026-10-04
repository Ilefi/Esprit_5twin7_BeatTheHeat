<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with the demo dataset (all names are fictional).
     *
     * Order matters: each seeder looks up the records created by the previous ones by slug, e-mail or code.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            // Gestion 1 — Produits & Certifications
            CategorySeeder::class,
            CertificationSeeder::class,
            // Gestion 2 — Chaîne de traçabilité (actors are needed by products)
            ActorSeeder::class,
            ProductSeeder::class,
            BatchSeeder::class,
            CertificationVerificationSeeder::class,
            // Gestion 3 — Empreinte environnementale
            ImpactSeeder::class,
            EmissionFactorSeeder::class,
            // Gestion 4 — Signalements & Avis
            ReviewSeeder::class,
            ReportSeeder::class,
            // Public pages
            SiteContentSeeder::class,
        ]);
    }
}
