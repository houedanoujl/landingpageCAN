# SOBOA CAN 2025

Application web de pronostics pour la Coupe d'Afrique des Nations 2025.

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
