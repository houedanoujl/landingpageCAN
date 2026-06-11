<?php

namespace Database\Seeders;

use App\Models\Bar;
use Illuminate\Database\Seeder;

/**
 * Ajoute les 4 nouveaux Points de Vente partenaires (juin 2026).
 *
 * Idempotent : relançable sans doublon (clé = nom du PDV).
 * Ne supprime ni ne modifie aucun autre PDV existant.
 *
 * Coordonnées GPS officielles fournies par le client (liens Google Maps,
 * 11/06/2026).
 */
class NewPartnerVenuesSeeder extends Seeder
{
    public function run(): void
    {
        $venues = [
            [
                'name' => 'PULLMAN HOTEL',
                'address' => 'Pullman Dakar Teranga, Place de l\'Indépendance, Dakar',
                'zone' => 'DAKAR PLATEAU',
                'latitude' => 14.6676661,
                'longitude' => -17.4310298,
                'type_pdv' => 'fanzone_hotel',
            ],
            [
                'name' => 'FOUR POINTS',
                'address' => 'Four Points by Sheraton Dakar Diamniadio',
                'zone' => 'DIAMNIADIO',
                'latitude' => 14.7377981,
                'longitude' => -17.2184445,
                'type_pdv' => 'fanzone_hotel',
            ],
            [
                'name' => 'HOTEL NOVOTEL',
                'address' => 'Hôtel Novotel Dakar, Avenue Abdoulaye Fadiga, Dakar',
                'zone' => 'DAKAR PLATEAU',
                'latitude' => 14.6687241,
                'longitude' => -17.4268024,
                'type_pdv' => 'fanzone_hotel',
            ],
            [
                'name' => 'VIP LOUNGE SALY',
                'address' => 'VIP Lounge Saly Officiel, Saly Portudal, Mbour',
                'zone' => 'SALY',
                'latitude' => 14.4410114,
                'longitude' => -17.0145167,
                'type_pdv' => 'chr',
            ],
        ];

        foreach ($venues as $venue) {
            $bar = Bar::updateOrCreate(
                ['name' => $venue['name']],
                $venue + ['is_active' => true]
            );

            $this->command->info(
                ($bar->wasRecentlyCreated ? '✅ PDV créé : ' : '♻️  PDV mis à jour : ') . $bar->name
            );
        }
    }
}
