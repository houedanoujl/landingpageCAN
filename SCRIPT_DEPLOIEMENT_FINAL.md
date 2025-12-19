# 🚀 Script de Déploiement Final - Production CAN 2025

## ⚠️ IMPORTANT: Nettoyage Préalable

Avant de déployer, il faut nettoyer les anciennes données incohérentes:

### Script Forge de Déploiement Complet

```bash
#!/bin/bash

$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY

# Installation dépendances
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Frontend
npm ci
npm run build

# ==========================================
# MIGRATIONS
# ==========================================
echo "🔄 Running migrations..."
$FORGE_PHP artisan migrate --force

# ==========================================
# PRODUCTION-SAFE SEEDING
# ==========================================
# ✅ Utilise updateOrCreate() au lieu de truncate()
# ✅ Préserve : users, predictions, user_points
# ✅ Met à jour : teams, matches, venues, animations
# ✅ Idempotent : peut être exécuté plusieurs fois

echo "🌱 Production-safe seeding..."
$FORGE_PHP artisan db:seed --class=ProductionSafeSeeder --force

# ✅ Garanties de Sécurité :
# - Users préservés (aucune suppression)
# - Predictions préservées (pas de cascade delete)
# - User points préservés
# - updateOrCreate() au lieu de truncate()
# - Transactions avec rollback automatique
# - Vérification de l'intégrité des données en fin de seeding

# ==========================================
# OPTIMISATIONS
# ==========================================
echo "🔧 Optimizing..."
$FORGE_PHP artisan optimize
$FORGE_PHP artisan storage:link

# Clear caches
$FORGE_PHP artisan config:clear
$FORGE_PHP artisan cache:clear
$FORGE_PHP artisan view:clear
$FORGE_PHP artisan route:clear

$ACTIVATE_RELEASE()

$RESTART_QUEUES()

echo "✅ Deployment completed!"
```

---

## 📊 Résultats Attendus

Après déploiement, vous devriez avoir:

| Ressource | Quantité | Description |
|-----------|----------|-------------|
| **Teams** | 24 | Équipes nationales africaines |
| **Stadiums** | 6+ | Stades de la CAN |
| **Matches** | 25+ | Matchs de poules + knockout |
| **Venues** | 60 | Points de vente avec coordonnées |
| **Animations** | 62 | Liens venue-match valides |

---

## 🧪 Test Local Final

Avant de déployer en production:

```bash
# Créer des users et predictions de test pour vérifier la sécurité
docker exec -w /app landingpagecan-laravel.test-1 php artisan tinker --execute="
    echo 'Creating test user and prediction...' . PHP_EOL;
    \$user = \App\Models\User::firstOrCreate(
        ['email' => 'test@test.com'],
        ['name' => 'Test User', 'password' => bcrypt('password')]
    );
    echo 'User created/found: ' . \$user->email . PHP_EOL;

    \$match = \App\Models\MatchGame::first();
    if (\$match) {
        \$prediction = \App\Models\Prediction::firstOrCreate(
            ['user_id' => \$user->id, 'match_id' => \$match->id],
            ['score_a' => 2, 'score_b' => 1]
        );
        echo 'Prediction created/found for match: ' . \$match->team_a . ' vs ' . \$match->team_b . PHP_EOL;
    }
"

# Compter AVANT seeding
docker exec -w /app landingpagecan-laravel.test-1 php artisan tinker --execute="
    echo 'BEFORE SEEDING:' . PHP_EOL;
    echo 'Users: ' . \App\Models\User::count() . PHP_EOL;
    echo 'Predictions: ' . \App\Models\Prediction::count() . PHP_EOL;
"

# Exécuter ProductionSafeSeeder (orchestrateur)
docker exec -w /app landingpagecan-laravel.test-1 php artisan db:seed --class=ProductionSafeSeeder

# ✅ ProductionSafeSeeder affichera automatiquement les statistiques complètes
# incluant la vérification de l'intégrité des users et predictions

# Vérification supplémentaire (optionnelle)
docker exec -w /app landingpagecan-laravel.test-1 php artisan tinker --execute="
    echo PHP_EOL . '=== ADDITIONAL VERIFICATION ===' . PHP_EOL;
    echo 'Teams: ' . \App\Models\Team::count() . ' (expected: 24)' . PHP_EOL;
    echo 'Stadiums: ' . \App\Models\Stadium::count() . PHP_EOL;
    echo 'Matches: ' . \App\Models\MatchGame::count() . ' (expected: 25+)' . PHP_EOL;
    echo 'Venues: ' . \App\Models\Bar::count() . ' (expected: 60)' . PHP_EOL;
    echo 'Venues with coords: ' . \App\Models\Bar::whereNotNull('latitude')->count() . ' (expected: 60)' . PHP_EOL;
    echo 'Animations: ' . \App\Models\Animation::count() . ' (expected: 62+)' . PHP_EOL;
    echo PHP_EOL . '🔒 CRITICAL - User Data:' . PHP_EOL;
    echo 'Users: ' . \App\Models\User::count() . ' (MUST BE PRESERVED!)' . PHP_EOL;
    echo 'Predictions: ' . \App\Models\Prediction::count() . ' (MUST BE PRESERVED!)' . PHP_EOL;
"
```

---

## ✅ Checklist Finale de Déploiement

### Avant le Déploiement
- [ ] Backup de la base de données production créé
- [ ] Script de déploiement Forge mis à jour avec nettoyage
- [ ] Tests locaux effectués et validés
- [ ] Commit et push vers Git

### Pendant le Déploiement
- [ ] Logs Forge surveillés en temps réel
- [ ] Aucune erreur dans les migrations
- [ ] Aucune erreur dans les seeders
- [ ] Nettoyage des animations invalides confirmé

### Après le Déploiement
- [ ] Vérification SSH des données (tinker)
- [ ] Test de la carte des venues (60 points)
- [ ] Test de la liste des matches (25+ matches)
- [ ] Test des animations (liens venue-match)
- [ ] Vérification que les users et predictions sont intacts

---

## 🎯 Commandes de Vérification Post-Déploiement

```bash
ssh forge@votresite.com
cd /home/forge/votresite.com

php artisan tinker --execute="
    echo '╔════════════════════════════════════════╗' . PHP_EOL;
    echo '║   PRODUCTION DATA VERIFICATION        ║' . PHP_EOL;
    echo '╚════════════════════════════════════════╝' . PHP_EOL . PHP_EOL;

    echo '📊 STATISTICS:' . PHP_EOL;
    echo '  Teams: ' . \App\Models\Team::count() . ' (expected: 24)' . PHP_EOL;
    echo '  Stadiums: ' . \App\Models\Stadium::count() . ' (expected: 6+)' . PHP_EOL;
    echo '  Matches: ' . \App\Models\MatchGame::count() . ' (expected: 25+)' . PHP_EOL;
    echo '  Venues: ' . \App\Models\Bar::count() . ' (expected: 60)' . PHP_EOL;
    echo '  Venues with coords: ' . \App\Models\Bar::whereNotNull('latitude')->count() . ' (expected: 60)' . PHP_EOL;
    echo '  Venues with zones: ' . \App\Models\Bar::whereNotNull('zone')->count() . ' (expected: 60)' . PHP_EOL;

    \$validAnimations = \App\Models\Animation::whereExists(function(\$q) {
        \$q->select(\Illuminate\Support\Facades\DB::raw(1))
          ->from('matches')
          ->whereColumn('matches.id', 'animations.match_id');
    })->count();

    echo '  Valid Animations: ' . \$validAnimations . ' (expected: 62)' . PHP_EOL . PHP_EOL;

    echo '👥 USER DATA (should remain unchanged):' . PHP_EOL;
    echo '  Users: ' . \App\Models\User::count() . PHP_EOL;
    echo '  Predictions: ' . \App\Models\Prediction::count() . PHP_EOL . PHP_EOL;

    echo '✅ SAMPLE DATA:' . PHP_EOL;
    \$animation = \App\Models\Animation::with(['bar', 'match'])->whereHas('match')->first();
    if (\$animation) {
        echo '  Sample: ' . \$animation->bar->name . ' (' . \$animation->bar->zone . ')' . PHP_EOL;
        echo '    → ' . \$animation->match->team_a . ' vs ' . \$animation->match->team_b . PHP_EOL;
        echo '    → Date: ' . \$animation->animation_date . ' ' . \$animation->animation_time . PHP_EOL;
        echo '    → Coords: ' . \$animation->bar->latitude . ', ' . \$animation->bar->longitude . PHP_EOL;
    }
"
```

---

## 🚨 Si Erreur Pendant le Déploiement

### Rollback Rapide

```bash
# SSH vers production
ssh forge@votresite.com
cd /home/forge/votresite.com

# Restaurer la DB depuis le backup
mysql -u forge -p nom_database < backup_YYYYMMDD_HHMMSS.sql

# Revenir au commit précédent
git reset --hard COMMIT_PRECEDENT
composer install --no-dev
npm ci && npm run build
php artisan optimize
```

---

## 📝 Commit Message Recommandé

```bash
git add database/seeders/FixAnimationsSeeder.php \
        forge-deployment-script.sh \
        GUIDE_DEPLOIEMENT_PRODUCTION.md \
        SCRIPT_DEPLOIEMENT_FINAL.md

git commit -m "feat: Production deployment - 60 venues + matches + animations

🎯 Deployment Components:
- TeamSeeder: 24 équipes nationales
- StadiumSeeder: 6+ stades CAN
- MatchSeeder: 25+ matchs (poules + knockout)
- FixAnimationsSeeder: 60 venues avec coordonnées OSM + 62 animations

✨ Features:
- Auto-creation of missing venues
- Improved team name matching (RDC vs RD Congo, Sénégal vs SENEGAL)
- Clean invalid animations before seeding
- Idempotent seeders (safe to re-run)

🔒 Safety:
- No user data affected
- No prediction data lost
- DB transactions with rollback
- Old invalid animations cleaned up

📊 Expected Production State:
- 60 venues with valid coordinates (Dakar region)
- All venues with zones assigned
- 62 venue-match links (animations)
- Geolocation map fully functional

See SCRIPT_DEPLOIEMENT_FINAL.md for deployment instructions."

git push origin main
```

---

## 🎊 Success Criteria

Déploiement réussi si:

✅ Aucune erreur dans les logs Forge
✅ 60 venues créés avec coordonnées
✅ 25+ matches créés
✅ 62 animations valides
✅ Users et predictions intacts
✅ Carte de géolocalisation fonctionnelle
✅ Pas de points dans l'océan

**Vous êtes prêt à déployer! 🚀**
