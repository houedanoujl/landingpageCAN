#!/bin/bash

# ============================================================================
# SCRIPT DE TEST DE CONNEXION PRODUCTION
# ============================================================================
# Utilisez ce script pour tester votre connexion à la production
# AVANT de lancer le reset complet
# ============================================================================

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

log_error() {
    echo -e "${RED}[✗]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

echo "=============================================="
echo "   TEST DE CONNEXION PRODUCTION"
echo "=============================================="
echo ""

# Test 1: Docker local
echo "1️⃣  Test de l'environnement local"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if docker compose ps | grep -q "laravel.test"; then
    log_success "Docker Compose est démarré"

    if docker compose exec -T mysql mysql -u sail -ppassword can_soboa -e "SELECT 1" &> /dev/null; then
        log_success "Base de données locale accessible"

        # Afficher les statistiques
        echo ""
        log_info "Statistiques de la base locale:"
        docker compose exec -T mysql mysql -u sail -ppassword can_soboa -e "
            SELECT 'Users' as 'Table', COUNT(*) as 'Lignes' FROM users
            UNION ALL SELECT 'Teams', COUNT(*) FROM teams
            UNION ALL SELECT 'Matches', COUNT(*) FROM matches
            UNION ALL SELECT 'Bars/PDV', COUNT(*) FROM bars
            UNION ALL SELECT 'Predictions', COUNT(*) FROM predictions;
        "
    else
        log_error "Impossible d'accéder à la base locale"
        exit 1
    fi
else
    log_error "Docker Compose n'est pas démarré"
    log_info "Lancez: ./vendor/bin/sail up -d"
    exit 1
fi

echo ""
echo "2️⃣  Test de la connexion production"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Choisissez votre méthode de connexion:"
echo "  1. SSH"
echo "  2. Connexion directe (via .env.production)"
echo ""
read -p "Méthode (1 ou 2): " method

if [ "$method" == "1" ]; then
    echo ""
    read -p "Serveur (ex: soboa.com): " host
    read -p "Utilisateur [forge]: " user
    user=${user:-forge}

    log_info "Test de connexion SSH vers $user@$host..."

    if ssh -o ConnectTimeout=10 "$user@$host" "echo 'OK'" &> /dev/null; then
        log_success "Connexion SSH établie"

        # Test de l'application Laravel
        log_info "Test de l'application Laravel..."
        ssh "$user@$host" "php --version" &> /dev/null && log_success "PHP accessible"

        log_info "Vérification des chemins..."
        ssh "$user@$host" "ls -la /home/forge/soboa-foot-time 2>/dev/null || ls -la ~" | head -5

    else
        log_error "Impossible de se connecter en SSH"
        log_info "Vérifiez:"
        log_info "  - Que le serveur est accessible"
        log_info "  - Que vos clés SSH sont configurées"
        log_info "  - Essayez: ssh -v $user@$host"
        exit 1
    fi

elif [ "$method" == "2" ]; then
    if [ ! -f ".env.production" ]; then
        log_error "Fichier .env.production introuvable"
        log_info "Créez-le depuis le template:"
        log_info "  cp .env.production.example .env.production"
        exit 1
    fi

    log_info "Chargement de .env.production..."
    source .env.production

    log_info "Test de connexion MySQL..."
    log_info "  Host: $DB_HOST"
    log_info "  Database: $DB_DATABASE"
    log_info "  User: $DB_USERNAME"
    echo ""

    if mysql -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "USE $DB_DATABASE; SELECT 1;" &> /dev/null; then
        log_success "Connexion MySQL établie"

        # Afficher les statistiques
        log_info "Statistiques de la base production:"
        mysql -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -e "
            SELECT 'Users' as 'Table', COUNT(*) as 'Lignes' FROM users
            UNION ALL SELECT 'Teams', COUNT(*) FROM teams
            UNION ALL SELECT 'Matches', COUNT(*) FROM matches
            UNION ALL SELECT 'Bars/PDV', COUNT(*) FROM bars
            UNION ALL SELECT 'Predictions', COUNT(*) FROM predictions;
        "
    else
        log_error "Impossible de se connecter à MySQL"
        log_info "Vérifiez les credentials dans .env.production"
        exit 1
    fi
else
    log_error "Option invalide"
    exit 1
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo -e "${GREEN}✅ Tous les tests sont OK!${NC}"
echo ""
echo "Vous pouvez maintenant lancer:"
echo "  ./reset-production-database.sh"
echo ""
