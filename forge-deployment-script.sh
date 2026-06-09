#!/bin/bash
set -e

# ==========================================
# DÉPLOIEMENT FORGE — SOBOA FOOT TIME
# CODE + MIGRATIONS (ne touche PAS aux données existantes)
# ==========================================

$CREATE_RELEASE()
cd $FORGE_RELEASE_DIRECTORY

echo "📦 Dépendances PHP..."
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "🎨 Build frontend..."
npm ci
npm run build

echo "🔄 Migrations..."
$FORGE_PHP artisan migrate --force

# ⚠️ NE JAMAIS mettre season:reset ou db:seed ici (wipe à chaque deploy).
# FreshDeploymentSeeder / deploy-production.sh truncate matches => détruisent
# pronostics ET commentaires. Reset compétition = UNE fois, manuellement.

echo "🧹 Clear caches..."
$FORGE_PHP artisan optimize:clear

echo "🔧 Optimize..."
$FORGE_PHP artisan optimize

echo "🔗 Storage link..."
$FORGE_PHP artisan storage:link || true

$ACTIVATE_RELEASE()
$RESTART_QUEUES()

echo "✅ Déploiement terminé."
