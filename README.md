# SOBOA Grande Fête du Foot Africain

Application web de pronostics pour la Grande Fête du Foot Africain 2025.

## Installation

```bash
# Cloner le projet
git clone https://github.com/jhouedanou/landingpageCAN.git
cd landingpageCAN

# Installer les dépendances
composer install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Lancer avec Docker
docker compose up -d

# Exécuter les migrations
docker compose exec laravel.test bash -c "cd /app && php artisan migrate --force"

# Seeder les équipes et matchs
docker compose exec laravel.test bash -c "cd /app && php artisan db:seed --class=TeamSeeder --force"
docker compose exec laravel.test bash -c "cd /app && php artisan db:seed --class=MatchSeeder --force"
```

## Configuration Firebase (Authentification SMS)

Pour activer l'authentification par SMS, ajoutez ces variables à votre fichier `.env` :

```env
FIREBASE_API_KEY=votre_api_key
FIREBASE_PROJECT_ID=votre_project_id
```

### Obtenir les clés Firebase :

1. Allez sur [Firebase Console](https://console.firebase.google.com/)
2. Créez un projet ou sélectionnez un projet existant
3. Activez **Authentication** > **Sign-in method** > **Phone**
4. Dans **Project Settings** > **General**, copiez :
   - `apiKey` → `FIREBASE_API_KEY`
   - `projectId` → `FIREBASE_PROJECT_ID`

## Dashboard Administrateur

Accédez au dashboard admin à `/admin` pour :
- Gérer les matchs (scores, statuts)
- Voir les utilisateurs et leurs points
- Déclencher le calcul des points

⚠️ **Accès admin** : L'utilisateur doit avoir `role = 'admin'` dans la table `users`.

```sql
UPDATE users SET role = 'admin' WHERE phone_number = '+225XXXXXXXXXX';
```

## Système de Points

| Action | Points |
|--------|--------|
| Participation (pronostic) | +1 |
| Bon vainqueur | +3 |
| Score exact | +3 |
| Visite lieu partenaire | +4/jour |

**Maximum par match : 7 points**

## URLs

- `/` - Accueil
- `/matches` - Liste des matchs et pronostics
- `/leaderboard` - Classement
- `/map` - Lieux partenaires
- `/dashboard` - Tableau de bord utilisateur
- `/admin` - Dashboard administrateur

## Tech Stack

- Laravel 11
- Tailwind CSS
- Alpine.js
- Firebase Auth (SMS)
- MySQL


## Déploiement et Gestion de la Base de Données

### Scripts de déploiement disponibles

#### 1. Reset complet de la production (⚠️ ATTENTION)

##### Pour Laravel Forge (RECOMMANDÉ) 🚀

Si vous utilisez Laravel Forge pour le déploiement :

```bash
# Tester d'abord la connexion
./test-production-connection.sh

# Puis lancer le reset
./reset-production-forge.sh
```

**Ce script va :**
- ✅ Créer une sauvegarde de la production (sur Forge + local)
- ✅ Exporter vos données locales (Docker Sail)
- ✅ Uploader vers Forge via SSH
- ✅ Importer en production (ÉCRASE TOUT)
- ✅ Nettoyer les caches Laravel automatiquement
- ✅ Vérifier l'import

📖 **Documentation Forge** : Voir [FORGE_RESET_GUIDE.md](./FORGE_RESET_GUIDE.md)

##### Pour serveur générique

Pour autres environnements (VPS, serveur dédié, etc.) :

```bash
./reset-production-database.sh
```

📖 **Documentation complète** : Voir [RESET_PRODUCTION_GUIDE.md](./RESET_PRODUCTION_GUIDE.md)

#### 2. Synchronisation sélective

Pour plus de contrôle, utilisez le script interactif :

```bash
./sync-database.sh
```

Options disponibles :
- Backup local/production
- Sync complète
- Sync sécurisée (préserve users)
- Sync données uniquement (teams, matchs, PDV)
- Comparaison local vs production

#### 3. Déploiement complet (code + base)

Pour déployer code ET base de données :

```bash
./deploy-production.sh
```

### Commandes manuelles sur Production

Si vous préférez exécuter manuellement :

```bash
# Sur le serveur de production
cd /home/forge/landingpagecan-qlrx6mvs.on-forge.com/current && \
php artisan db:backup && \
php artisan migrate --force && \
php artisan tinker --execute="DB::statement('SET FOREIGN_KEY_CHECKS=0');DB::table('animations')->truncate();DB::table('matches')->truncate();DB::table('bars')->truncate();DB::table('stadiums')->truncate();DB::table('teams')->truncate();DB::statement('SET FOREIGN_KEY_CHECKS=1');" && \
php artisan db:seed --class=AllCANTeamsSeeder --force && \
php artisan db:seed --class=StadiumSeeder --force && \
php artisan db:seed --class=BarSeeder --force && \
php artisan db:seed --class=MatchSeeder --force && \
php artisan db:seed --class=AnimationSeeder --force && \
php artisan cache:clear && \
php artisan config:clear && \
php artisan tinker --execute="echo 'Teams: '.\App\Models\Team::count().' | Venues: '.\App\Models\Bar::count().' | Matches: '.\App\Models\MatchGame::count();" && \
echo "✅ Synchronisation terminée!"
```

### Configuration pour la production

Créez un fichier `.env.production` (déjà dans .gitignore) :

```bash
cp .env.production.example .env.production
# Puis éditez avec vos vraies valeurs
```