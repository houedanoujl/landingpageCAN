<?php

namespace Database\Seeders;

use App\Models\Bar;
use Illuminate\Database\Seeder;

class BarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $venues = [
            // Cocody
            [
                'name' => 'Maquis La Terrasse - Cocody',
                'address' => 'Cocody Riviera 2, près du carrefour Palmeraie, Abidjan',
                'latitude' => 5.35837443273195,
                'longitude' => -3.9439878409347893,
                'is_active' => true,
            ],
            [
                'name' => 'Bar Restaurant Chez Amani',
                'address' => 'Cocody Angré 8ème Tranche, Abidjan',
                'latitude' => 5.3738,
                'longitude' => -3.9892,
                'is_active' => true,
            ],
            [
                'name' => 'Le Petit Jardin - Cocody',
                'address' => 'Cocody II Plateaux Vallon, Abidjan',
                'latitude' => 5.3621,
                'longitude' => -4.0012,
                'is_active' => true,
            ],

            // Plateau
            [
                'name' => 'Brasserie du Plateau',
                'address' => 'Boulevard de la République, Plateau, Abidjan',
                'latitude' => 5.3167,
                'longitude' => -4.0167,
                'is_active' => true,
            ],
            [
                'name' => 'Le Central - Plateau',
                'address' => 'Avenue Franchet d\'Esperey, Plateau, Abidjan',
                'latitude' => 5.3201,
                'longitude' => -4.0189,
                'is_active' => true,
            ],

            // Marcory
            [
                'name' => 'Maquis Le Baobab - Marcory',
                'address' => 'Zone 4, Marcory, Abidjan',
                'latitude' => 5.3012,
                'longitude' => -3.9834,
                'is_active' => true,
            ],
            [
                'name' => 'Espace Détente Marcory',
                'address' => 'Boulevard VGE, Marcory, Abidjan',
                'latitude' => 5.2987,
                'longitude' => -3.9756,
                'is_active' => true,
            ],

            // Yopougon
            [
                'name' => 'Le Grand Maquis - Yopougon',
                'address' => 'Yopougon Maroc, près du CHU, Abidjan',
                'latitude' => 5.3589,
                'longitude' => -4.0834,
                'is_active' => true,
            ],
            [
                'name' => 'Bar La Joie - Yopougon',
                'address' => 'Yopougon Selmer, Abidjan',
                'latitude' => 5.3456,
                'longitude' => -4.0712,
                'is_active' => true,
            ],
            [
                'name' => 'Chez Dago - Yopougon',
                'address' => 'Yopougon Siporex, Abidjan',
                'latitude' => 5.3523,
                'longitude' => -4.0923,
                'is_active' => true,
            ],

            // Treichville
            [
                'name' => 'Maquis Bord de Lagune',
                'address' => 'Avenue 12, Treichville, Abidjan',
                'latitude' => 5.2934,
                'longitude' => -4.0023,
                'is_active' => true,
            ],
            [
                'name' => 'Le Terminus - Treichville',
                'address' => 'Près de la Gare de Treichville, Abidjan',
                'latitude' => 5.2912,
                'longitude' => -3.9967,
                'is_active' => true,
            ],

            // Adjamé
            [
                'name' => 'Espace CAN - Adjamé',
                'address' => 'Adjamé Liberté, près du marché, Abidjan',
                'latitude' => 5.3423,
                'longitude' => -4.0234,
                'is_active' => true,
            ],
            [
                'name' => 'Le Carrefour - Adjamé',
                'address' => 'Adjamé 220 Logements, Abidjan',
                'latitude' => 5.3512,
                'longitude' => -4.0312,
                'is_active' => true,
            ],

            // Koumassi
            [
                'name' => 'Maquis du Port - Koumassi',
                'address' => 'Koumassi Remblais, Abidjan',
                'latitude' => 5.2823,
                'longitude' => -3.9512,
                'is_active' => true,
            ],

            // Abobo
            [
                'name' => 'Le Populaire - Abobo',
                'address' => 'Abobo Baoulé, Abidjan',
                'latitude' => 5.4234,
                'longitude' => -4.0156,
                'is_active' => true,
            ],
            [
                'name' => 'Chez Koffi - Abobo',
                'address' => 'Abobo Gare, près du marché, Abidjan',
                'latitude' => 5.4312,
                'longitude' => -4.0234,
                'is_active' => true,
            ],

            // Port-Bouët
            [
                'name' => 'Beach Bar - Port-Bouët',
                'address' => 'Plage de Port-Bouët, Abidjan',
                'latitude' => 5.2534,
                'longitude' => -3.9234,
                'is_active' => true,
            ],
            [
                'name' => 'Le Phare - Port-Bouët',
                'address' => 'Près de l\'aéroport, Port-Bouët, Abidjan',
                'latitude' => 5.2612,
                'longitude' => -3.9345,
                'is_active' => true,
            ],

            // Bingerville
            [
                'name' => 'Maquis Colonial - Bingerville',
                'address' => 'Centre-ville, Bingerville',
                'latitude' => 5.3534,
                'longitude' => -3.8912,
                'is_active' => true,
            ],

            // Big Five Abidjan
            [
                'name' => 'Big Five Abidjan',
                'address' => 'Abidjan, Côte d\'Ivoire',
                'latitude' => 5.294918911991902,
                'longitude' => -3.99670027907965,
                'is_active' => true,
            ],
        ];

        foreach ($venues as $venue) {
            Bar::updateOrCreate(
                ['name' => $venue['name']],
                $venue
            );
        }

        $this->command->info('✅ ' . count($venues) . ' points de vente créés/mis à jour avec succès!');
        $this->command->info('📍 Zones couvertes: Cocody, Plateau, Marcory, Yopougon, Treichville, Adjamé, Koumassi, Abobo, Port-Bouët, Bingerville');
        $this->command->info('📏 Rayon de geofencing: 200 mètres');
    }
}
