#!/bin/bash

# ==========================================
# SCRIPT DE DÉPLOIEMENT FORGE - PRODUCTION
# SOBOA FOOT TIME - Le goût de notre victoire
# ==========================================

$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY

echo "📦 Installation des dépendances PHP..."
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "🎨 Installation et build du frontend (avec responsive fixes)..."
npm ci
npm run build

# ==========================================
# MIGRATIONS (SANS --seed global!)
# ==========================================

echo "🔄 Running migrations..."
$FORGE_PHP artisan migrate --force

# ==========================================
# FRESH DEPLOYMENT SEEDING (WITH CSV DATA)
# ==========================================
# Uses FreshDeploymentSeeder to import fresh data from venues.csv
# ✅ Preserves: users (user data intact)
# 🔄 Refreshes: teams, matches, venues, animations from CSV
# ⚠️  Note: Predictions will be reset for new matches

echo "🌱 Running FRESH DEPLOYMENT seeders (with CSV import)..."
$FORGE_PHP artisan db:seed --class=FreshDeploymentSeeder --force

echo "🔧 Optimizing application..."
$FORGE_PHP artisan optimize

echo "🔗 Creating storage link..."
$FORGE_PHP artisan storage:link

# ==========================================
# CACHE CLEARING (FIX 404 error!)
# ==========================================

echo "🧹 Clearing caches..."
$FORGE_PHP artisan config:clear
$FORGE_PHP artisan cache:clear
$FORGE_PHP artisan view:clear
$FORGE_PHP artisan route:clear  # ← CRITICAL: Fixes 404 on "modifier" link

$ACTIVATE_RELEASE()

$RESTART_QUEUES()

echo "✅ Deployment completed successfully!"
