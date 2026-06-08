#!/bin/bash

# Script de développement SOBOA FOOT TIME avec Hot Reload
# Lance PHP artisan serve + Vite en parallèle

echo "🚀 =============================================="
echo "   SOBOA FOOT TIME - Mode Développement (Hot Reload)"
echo "=============================================="
echo ""

# Vérifier si on est dans le bon répertoire
if [ ! -f "artisan" ]; then
    echo "❌ Erreur: Ce script doit être exécuté depuis la racine du projet Laravel"
    exit 1
fi

echo "📦 Démarrage des serveurs de développement..."
echo ""

# Fonction pour nettoyer les processus à l'arrêt
cleanup() {
    echo ""
    echo "🛑 Arrêt des serveurs..."
    kill $(jobs -p) 2>/dev/null
    exit 0
}

trap cleanup SIGINT SIGTERM

# Démarrer PHP artisan serve en arrière-plan
echo "🔧 Démarrage du serveur Laravel (http://localhost:8000)..."
php artisan serve > /dev/null 2>&1 &
LARAVEL_PID=$!

# Attendre que Laravel démarre
sleep 2

# Démarrer Vite en arrière-plan
echo "⚡ Démarrage du serveur Vite avec Hot Reload (http://localhost:5173)..."
echo ""
echo "✅ =============================================="
echo "   Serveurs démarrés avec succès!"
echo "=============================================="
echo ""
echo "📍 URLs:"
echo "   - Application: http://localhost:8000"
echo "   - Vite HMR:    http://localhost:5173"
echo ""
echo "🔥 Hot Reload activé pour:"
echo "   - Fichiers CSS (resources/css/**)"
echo "   - Fichiers JS (resources/js/**)"
echo "   - Fichiers Blade (resources/views/**)"
echo "   - Controllers (app/Http/Controllers/**)"
echo "   - Routes (routes/**)"
echo ""
echo "💡 Modifiez vos fichiers et le navigateur se rafraîchira automatiquement!"
echo ""
echo "⏹️  Pour arrêter: Appuyez sur Ctrl+C"
echo ""
echo "=============================================="
echo ""

# Démarrer Vite (en premier plan pour voir les logs)
npm run dev

# Si Vite s'arrête, arrêter Laravel aussi
cleanup
