<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Crée (ou met à jour) le compte SOBOA avec le rôle "soboa".
 *
 * Ce rôle donne accès à l'interface d'administration (middleware check.admin
 * accepte admin + soboa) : statistiques et gestion du contenu
 * « Animation SOBOA FOOT ».
 *
 * Idempotent : relançable sans doublon (clé = numéro de téléphone).
 *
 * Le numéro et le nom sont configurables via .env :
 *   SOBOA_ADMIN_PHONE=+221770000000
 *   SOBOA_ADMIN_NAME=Amadou
 */
class SoboaAdminSeeder extends Seeder
{
    public function run(): void
    {
        $phone = config('auth_phones.soboa_admin_phone');
        $name = config('auth_phones.soboa_admin_name', 'Amadou (SOBOA)');

        if (!$phone) {
            $this->command->error('❌ SOBOA_ADMIN_PHONE non défini dans .env — aucun compte créé.');
            $this->command->warn('   Ajouter SOBOA_ADMIN_PHONE=+221XXXXXXXXX puis relancer : php artisan db:seed --class=SoboaAdminSeeder');
            return;
        }

        $user = User::where('phone', $phone)->first();

        if ($user) {
            if ($user->role !== 'soboa' && $user->role !== 'admin') {
                $user->update(['role' => 'soboa']);
                $this->command->info("✅ Rôle soboa attribué à {$user->name} ({$phone})");
            } else {
                $this->command->info("✅ {$user->name} ({$phone}) a déjà un accès admin (rôle {$user->role})");
            }
            return;
        }

        $password = str_replace(['0', 'O', 'l', '1'], ['3', 'A', 'k', '7'], substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 10));

        $user = User::create([
            'name' => $name,
            'phone' => $phone,
            'password' => Hash::make($password),
            'role' => 'soboa',
            'points_total' => 0,
        ]);
        $user->setPlainPassword($password);
        $user->save();

        $this->command->info("✅ Compte SOBOA créé : {$name} ({$phone})");
        $this->command->warn("   Mot de passe initial : {$password} — à transmettre de façon sécurisée puis à changer.");
    }
}
