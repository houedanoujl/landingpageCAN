#!/bin/bash

# ==========================================
# SCRIPT DE DÉPLOIEMENT FORGE - PRODUCTION
# GAZELLE - Le goût de notre victoire
# ==========================================
# MODE: CODE ONLY - Ne touche PAS à la base de données
# ==========================================

$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY

echo "📦 Installation des dépendances PHP..."
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "🎨 Installation et build du frontend..."
npm ci
npm run build

# ==========================================
# MIGRATIONS - DÉSACTIVÉES
# ==========================================
# ⚠️ Les migrations sont désactivées pour préserver la base de données
# Les données existantes (matchs, animations, PDV, équipes, utilisateurs)
# ne seront pas affectées par le déploiement.
#
# Si vous devez exécuter une migration manuellement:
# ssh forge@your-server
# cd /home/forge/your-site/current
# php artisan migrate --force
# ==========================================

# echo "🔄 Running migrations..."
# $FORGE_PHP artisan migrate --force

# ==========================================
# PRODUCTION SEEDING - DÉSACTIVÉ DÉFINITIVEMENT
# ==========================================
# ⚠️ NE JAMAIS EXÉCUTER LE SEEDER EN PRODUCTION !
# 
# Le ProductionSeeder SUPPRIME DÉFINITIVEMENT:
# - Toutes les animations (perdues définitivement)
# - Tous les matchs
# - Tous les PDV
# - Toutes les équipes
#
# Pour ajouter des données en production, utilisez:
# 1. L'interface admin: /admin/bars (import CSV)
# 2. L'interface admin: /admin/matches (création manuelle)
#
# Le CSV d'import supporte maintenant les animations:
# nom,adresse,latitude,longitude,TYPE_PDV,DATE_ANIMATION,HEURE_ANIMATION,EQUIPE_A,EQUIPE_B
# ==========================================

# echo "🌱 Running PRODUCTION seeders..."
# $FORGE_PHP artisan db:seed --class=ProductionSeeder --force

# ==========================================
# CACHE CLEARING (CRITICAL - avant optimize!)
# ==========================================

echo "🧹 Clearing ALL caches..."
$FORGE_PHP artisan config:clear
$FORGE_PHP artisan cache:clear
$FORGE_PHP artisan view:clear
$FORGE_PHP artisan route:clear
$FORGE_PHP artisan event:clear

echo "🔧 Optimizing application..."
$FORGE_PHP artisan optimize

echo "🔗 Creating storage link..."
$FORGE_PHP artisan storage:link

$ACTIVATE_RELEASE()

$RESTART_QUEUES()

echo "✅ Deployment completed successfully (CODE ONLY - DB unchanged)!"