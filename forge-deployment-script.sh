#!/bin/bash

# ==========================================
# SCRIPT DE DÉPLOIEMENT FORGE - PRODUCTION
# ==========================================

$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY

# Installation des dépendances PHP
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Installation et build du frontend
npm ci
npm run build

# ==========================================
# MIGRATIONS (SANS --seed global!)
# ==========================================

echo "🔄 Running migrations..."
$FORGE_PHP artisan migrate --force

# ==========================================
# PRODUCTION-SAFE SEEDING
# ==========================================
# Uses ProductionSafeSeeder instead of individual seeders
# CRITICAL: Preserves users and predictions (no truncate!)

echo "🌱 Running PRODUCTION-SAFE seeders..."
$FORGE_PHP artisan db:seed --class=ProductionSafeSeeder --force

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
