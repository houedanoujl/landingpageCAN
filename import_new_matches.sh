#!/bin/bash

# Script d'importation des nouveaux matchs SOBOA FOOT TIME
# Ce script nettoie les données existantes et importe les nouveaux matchs depuis le CSV

echo "🚀 =============================================="
echo "   SOBOA FOOT TIME - Import des Nouveaux Matchs"
echo "=============================================="
echo ""

# Vérifier si on est dans le bon répertoire
if [ ! -f "artisan" ]; then
    echo "❌ Erreur: Ce script doit être exécuté depuis la racine du projet Laravel"
    exit 1
fi

# Demander confirmation
echo "⚠️  ATTENTION: Ce script va:"
echo "   - Supprimer tous les matchs existants"
echo "   - Supprimer toutes les prédictions"
echo "   - Supprimer tous les bars/venues"
echo "   - Supprimer les points logs liés aux matchs et bars"
echo "   - Importer les nouveaux matchs depuis le CSV"
echo ""
read -p "Êtes-vous sûr de vouloir continuer? (oui/non): " confirmation

if [ "$confirmation" != "oui" ]; then
    echo "❌ Import annulé"
    exit 0
fi

echo ""
echo "📦 Démarrage de l'importation..."
echo ""

# Exécuter le seeder
php artisan db:seed --class=NewMatchesSeeder

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ =============================================="
    echo "   Import terminé avec succès!"
    echo "=============================================="
    echo ""
    echo "📊 Prochaines étapes:"
    echo "   1. Vérifier les données sur /admin/matches"
    echo "   2. Vérifier les bars sur /admin/bars"
    echo "   3. Tester l'affichage des matchs sur /matches"
    echo ""
else
    echo ""
    echo "❌ =============================================="
    echo "   Erreur lors de l'import"
    echo "=============================================="
    echo ""
    echo "Veuillez vérifier les logs pour plus de détails"
    exit 1
fi
