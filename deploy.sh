#!/bin/bash
set -e  # fail fast on first error

# ==========================================
# DEPLOY SCRIPT — LARAVEL FORGE / PRODUCTION
# SOBOA FOOT TIME
# ==========================================
# Idempotent: safe to re-run.
# Caches and assets are rebuilt every release for parity dev ↔ prod.
# ==========================================

$CREATE_RELEASE()

cd $FORGE_RELEASE_DIRECTORY

# ------------------------------------------
# 1. PHP DEPENDENCIES
# ------------------------------------------
echo "📦 Installing PHP dependencies (production)..."
$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# ------------------------------------------
# 2. FRONTEND BUILD
# ------------------------------------------
# `npm ci` uses package-lock.json for deterministic installs (same versions
# everywhere). `npm run build` regenerates Tailwind, Vite assets, manifest.
echo "🎨 Installing and building frontend assets..."
npm ci
npm run build

# ------------------------------------------
# 3. DATABASE MIGRATIONS
# ------------------------------------------
# Required for: add_external_id_to_matches_table + any future schema change.
# `--force` bypasses the production prompt. Migrations are idempotent.
echo "🔄 Running database migrations..."
$FORGE_PHP artisan migrate --force

# ==========================================
# PRODUCTION SEEDING — DISABLED PERMANENTLY
# ==========================================
# ⚠️ NEVER run ProductionSeeder in prod — it WIPES animations, matches, PDV,
#    teams. Add data via /admin/bars (CSV import) or /admin/matches.
# ==========================================

# ------------------------------------------
# 4. CLEAR ALL CACHES (before rebuilding)
# ------------------------------------------
echo "🧹 Clearing caches..."
$FORGE_PHP artisan optimize:clear

# ------------------------------------------
# 5. REBUILD CACHES FOR PRODUCTION
# ------------------------------------------
# `optimize` runs config:cache + route:cache + view:cache + event:cache.
# Keeps responses fast and avoids per-request file reads.
echo "🔧 Rebuilding optimized caches..."
$FORGE_PHP artisan optimize

# ------------------------------------------
# 6. STORAGE LINK
# ------------------------------------------
# Idempotent: re-creates only if missing.
echo "🔗 Ensuring storage symlink..."
$FORGE_PHP artisan storage:link || true

$ACTIVATE_RELEASE()

# ------------------------------------------
# 7. QUEUE WORKERS
# ------------------------------------------
# Forge restarts queue daemons here. Without it, workers keep running OLD code.
$RESTART_QUEUES()

# ------------------------------------------
# 8. POST-DEPLOY CHECKLIST (one-time on the Forge server, not via this script)
# ------------------------------------------
# ☐ Forge → Site → Scheduler → enable `php artisan schedule:run` every minute.
# ☐ Forge → Site → Environment → set:
#     APP_ENV=production
#     APP_DEBUG=false
#     APP_TIMEZONE=UTC          (= GMT)
#     APP_URL=https://<domain>
#     FOOTBALL_DATA_ENABLED=true
#     FOOTBALL_DATA_API_KEY=<your key from football-data.org>
#     FOOTBALL_DATA_COMPETITION=WC     (FIFA World Cup)
# ☐ Forge → Site → Queue Workers → 1 worker minimum.
# ☐ DNS / SSL configured.

echo "✅ Deployment finished. Release activated."
