<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder de déploiement « tout-en-un » pour les PDV partenaires.
 *
 * Enchaîne dans le bon ordre :
 *   1. NewPartnerVenuesSeeder          — crée/met à jour les 4 nouveaux PDV
 *   2. AssociateAllMatchesToAllPdvsSeeder — associe tous les matchs à tous les PDV actifs
 *
 * L'ordre est garanti : impossible d'oublier l'association après l'ajout
 * des PDV. Idempotent : relançable sans doublon.
 *
 * Production :
 *   php artisan db:seed --class=PartnerVenuesDeploymentSeeder --force
 */
class PartnerVenuesDeploymentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            NewPartnerVenuesSeeder::class,
            AssociateAllMatchesToAllPdvsSeeder::class,
        ]);
    }
}
