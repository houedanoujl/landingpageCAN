#!/bin/bash

echo "🧹 Nettoyage complet des caches..."
echo ""

# Arrêter le serveur Vite s'il tourne
echo "📍 Arrêt de Vite (si actif)..."
pkill -f "vite" || true

# Vider tous les caches Laravel
echo "🗑️  Vidage des caches Laravel..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Supprimer les fichiers compilés
echo "🗑️  Suppression des fichiers compilés..."
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*
rm -rf storage/framework/sessions/*

# Nettoyer node_modules et public/build
echo "🗑️  Nettoyage des assets frontend..."
rm -rf node_modules/.vite
rm -rf public/build
rm -rf public/hot

# Rebuild des assets
echo "🔨 Rebuild des assets Tailwind + Vite..."
npm install
npm run build

echo ""
echo "✅ Nettoyage terminé!"
echo ""
echo "🚀 Prochaines étapes:"
echo "   1. Lancez: php artisan serve"
echo "   2. Dans un autre terminal: npm run dev"
echo "   3. Ouvrez votre navigateur et faites Ctrl+Shift+R (hard refresh)"
echo ""
