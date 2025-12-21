#!/bin/bash

# ============================================================================
# SCRIPT DE RESET PRODUCTION - OPTIMISÉ POUR LARAVEL FORGE
# ============================================================================
# Ce script ÉCRASE complètement la base de données production Forge
# avec vos données locales (Docker Sail)
#
# ⚠️  UTILISATION AVEC PRÉCAUTION - Action irréversible! ⚠️
# ============================================================================

set -e  # Arrêter en cas d'erreur

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="$SCRIPT_DIR/storage/backups"
TEMP_DIR="/tmp/db_reset_$TIMESTAMP"

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
NC='\033[0m'

# Configuration Forge (depuis .env.production)
if [ ! -f "$SCRIPT_DIR/.env.production" ]; then
    echo -e "${RED}Erreur: .env.production introuvable${NC}"
    echo "Créez-le d'abord avec vos credentials Forge"
    exit 1
fi

source "$SCRIPT_DIR/.env.production"

# Vérifier les variables
if [ -z "$PRODUCTION_HOST" ] || [ -z "$PRODUCTION_USER" ] || [ -z "$PRODUCTION_PATH" ]; then
    echo -e "${RED}Erreur: Configuration Forge incomplète dans .env.production${NC}"
    echo "Vérifiez que ces variables sont définies:"
    echo "  - PRODUCTION_HOST"
    echo "  - PRODUCTION_USER"
    echo "  - PRODUCTION_PATH"
    exit 1
fi

# Fonctions utilitaires
log_header() {
    echo ""
    echo -e "${MAGENTA}╔════════════════════════════════════════════════════════╗${NC}"
    printf "${MAGENTA}║${NC}   %-50s ${MAGENTA}║${NC}\n" "$1"
    echo -e "${MAGENTA}╚════════════════════════════════════════════════════════╝${NC}"
    echo ""
}

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[⚠]${NC} $1"
}

log_error() {
    echo -e "${RED}[✗]${NC} $1"
}

log_step() {
    echo ""
    echo -e "${BLUE}▶${NC} $1"
    echo -e "${BLUE}$(printf '%.0s─' {1..60})${NC}"
}

# Afficher l'avertissement
show_warning() {
    log_header "⚠️  AVERTISSEMENT DE SÉCURITÉ ⚠️"

    echo -e "${RED}ATTENTION: Ce script va:${NC}"
    echo -e "${RED}  1. ❌ SUPPRIMER toutes les données de production sur Forge${NC}"
    echo -e "${RED}  2. 🔄 Les remplacer par vos données locales${NC}"
    echo -e "${RED}  3. ⚠️  Écraser: Users, Predictions, Teams, Matchs, etc.${NC}"
    echo ""
    echo -e "${BLUE}Configuration Forge:${NC}"
    echo -e "${BLUE}  📡 Host: $PRODUCTION_HOST${NC}"
    echo -e "${BLUE}  👤 User: $PRODUCTION_USER${NC}"
    echo -e "${BLUE}  📁 Path: $PRODUCTION_PATH${NC}"
    echo ""
    echo -e "${GREEN}Protection:${NC}"
    echo -e "${GREEN}  ✓ Un backup de production sera créé avant toute action${NC}"
    echo -e "${GREEN}  ✓ Sauvegardé sur Forge ET localement${NC}"
    echo ""

    read -p "Avez-vous lu et compris cet avertissement? (oui/non): " understood
    if [ "$understood" != "oui" ]; then
        log_error "Opération annulée"
        exit 0
    fi
}

# Vérifier les prérequis
check_requirements() {
    log_step "Vérification des prérequis"

    # Vérifier Docker local
    if ! docker compose ps | grep -q "laravel.test"; then
        log_error "Docker Compose n'est pas démarré"
        log_info "Lancez: ./vendor/bin/sail up -d"
        exit 1
    fi
    log_success "Docker Compose: OK"

    # Vérifier la base locale
    if ! docker compose exec -T mysql mysql -u sail -ppassword can_soboa -e "SELECT 1" &> /dev/null; then
        log_error "Impossible d'accéder à la base locale"
        exit 1
    fi
    log_success "Base de données locale: OK"

    # Vérifier SSH
    log_info "Test de connexion SSH vers Forge..."
    if ! ssh -o ConnectTimeout=10 "$PRODUCTION_USER@$PRODUCTION_HOST" "echo 'OK'" &> /dev/null; then
        log_error "Impossible de se connecter à $PRODUCTION_USER@$PRODUCTION_HOST"
        log_info "Vérifiez:"
        log_info "  1. Que vous pouvez vous connecter: ssh $PRODUCTION_USER@$PRODUCTION_HOST"
        log_info "  2. Vos clés SSH sont configurées dans Forge"
        exit 1
    fi
    log_success "Connexion SSH Forge: OK"

    # Vérifier que le chemin existe
    if ! ssh "$PRODUCTION_USER@$PRODUCTION_HOST" "[ -d '$PRODUCTION_PATH' ]" &> /dev/null; then
        log_error "Le chemin $PRODUCTION_PATH n'existe pas sur le serveur"
        log_info "Vérifiez PRODUCTION_PATH dans .env.production"
        exit 1
    fi
    log_success "Chemin application: OK"

    # Créer les dossiers
    mkdir -p "$BACKUP_DIR"
    mkdir -p "$TEMP_DIR"

    log_success "Prérequis validés"
}

# Statistiques locales
show_local_stats() {
    log_step "Statistiques de la base de données LOCALE"

    docker compose exec -T mysql mysql -u sail -ppassword can_soboa -e "
        SELECT
            'Users' as 'Table',
            COUNT(*) as 'Lignes'
        FROM users
        UNION ALL
        SELECT 'Teams', COUNT(*) FROM teams
        UNION ALL
        SELECT 'Matches', COUNT(*) FROM matches
        UNION ALL
        SELECT 'Bars/PDV', COUNT(*) FROM bars
        UNION ALL
        SELECT 'Predictions', COUNT(*) FROM predictions
        UNION ALL
        SELECT 'Animations', COUNT(*) FROM animations
        UNION ALL
        SELECT 'Point Logs', COUNT(*) FROM point_logs;
    " 2>/dev/null || log_warning "Impossible d'afficher les stats"

    echo ""
    log_warning "Ces données vont REMPLACER celles de production Forge!"
}

# Statistiques production
show_production_stats() {
    log_step "Statistiques de la base de données PRODUCTION (avant reset)"

    ssh "$PRODUCTION_USER@$PRODUCTION_HOST" << EOF
        cd "$PRODUCTION_PATH"
        source .env

        mysql -h "\$DB_HOST" -u "\$DB_USERNAME" -p"\$DB_PASSWORD" "\$DB_DATABASE" -e "
            SELECT
                'Users' as 'Table',
                COUNT(*) as 'Lignes'
            FROM users
            UNION ALL
            SELECT 'Teams', COUNT(*) FROM teams
            UNION ALL
            SELECT 'Matches', COUNT(*) FROM matches
            UNION ALL
            SELECT 'Bars/PDV', COUNT(*) FROM bars
            UNION ALL
            SELECT 'Predictions', COUNT(*) FROM predictions
            UNION ALL
            SELECT 'Animations', COUNT(*) FROM animations;
        " 2>/dev/null || echo "Impossible d'afficher les stats"
EOF
}

# Backup de la production
backup_production() {
    log_step "Sauvegarde de la base de données PRODUCTION"

    PROD_BACKUP="$BACKUP_DIR/forge_production_backup_$TIMESTAMP.sql"

    log_info "Création du backup sur Forge..."

    # Créer le backup sur le serveur
    ssh "$PRODUCTION_USER@$PRODUCTION_HOST" << EOF
        set -e
        cd "$PRODUCTION_PATH"
        mkdir -p storage/backups

        source .env

        echo "📦 Dump de la base production..."
        mysqldump -h "\$DB_HOST" -u "\$DB_USERNAME" -p"\$DB_PASSWORD" "\$DB_DATABASE" \
            --single-transaction \
            --quick \
            --lock-tables=false \
            > storage/backups/pre_reset_$TIMESTAMP.sql

        echo "Backup créé: storage/backups/pre_reset_$TIMESTAMP.sql"
        ls -lh storage/backups/pre_reset_$TIMESTAMP.sql
EOF

    # Télécharger le backup localement
    log_info "Téléchargement du backup en local..."
    scp "$PRODUCTION_USER@$PRODUCTION_HOST:$PRODUCTION_PATH/storage/backups/pre_reset_$TIMESTAMP.sql" "$PROD_BACKUP"

    BACKUP_SIZE=$(du -h "$PROD_BACKUP" | cut -f1)
    log_success "Backup production sauvegardé: $PROD_BACKUP ($BACKUP_SIZE)"

    echo ""
    log_info "📍 Backup sur Forge: $PRODUCTION_PATH/storage/backups/pre_reset_$TIMESTAMP.sql"
    log_info "📍 Backup local: $PROD_BACKUP"
    echo ""
}

# Export de la base locale
export_local_database() {
    log_step "Export de la base de données LOCALE"

    LOCAL_EXPORT="$TEMP_DIR/local_full_export.sql"

    log_info "Création du dump MySQL depuis Docker..."
    docker compose exec -T mysql mysqldump \
        -u sail \
        -ppassword \
        can_soboa \
        --single-transaction \
        --quick \
        --lock-tables=false \
        > "$LOCAL_EXPORT"

    if [ ! -f "$LOCAL_EXPORT" ]; then
        log_error "Échec de l'export local"
        exit 1
    fi

    EXPORT_SIZE=$(du -h "$LOCAL_EXPORT" | cut -f1)
    log_success "Export local créé: $LOCAL_EXPORT ($EXPORT_SIZE)"
}

# Import en production
import_to_production() {
    log_step "Import des données en PRODUCTION FORGE"

    log_warning "⚠️  DERNIÈRE CHANCE D'ANNULER ⚠️"
    log_warning "La base de production va être ÉCRASÉE dans 5 secondes..."
    echo ""

    for i in 5 4 3 2 1; do
        echo -ne "\r${RED}[$i]${NC} Ctrl+C pour annuler..."
        sleep 1
    done
    echo ""
    echo ""

    LOCAL_EXPORT="$TEMP_DIR/local_full_export.sql"

    # Upload du dump vers Forge
    log_info "📤 Upload du dump vers Forge..."
    scp "$LOCAL_EXPORT" "$PRODUCTION_USER@$PRODUCTION_HOST:$PRODUCTION_PATH/storage/app/reset_import_$TIMESTAMP.sql"
    log_success "Upload terminé"

    # Import sur Forge
    log_info "📥 Import en cours sur Forge..."
    ssh "$PRODUCTION_USER@$PRODUCTION_HOST" << EOF
        set -e
        cd "$PRODUCTION_PATH"
        source .env

        echo "🗑️  Suppression de toutes les données..."
        mysql -h "\$DB_HOST" -u "\$DB_USERNAME" -p"\$DB_PASSWORD" "\$DB_DATABASE" << 'SQLEOF'
SET FOREIGN_KEY_CHECKS = 0;

-- Lister toutes les tables
SET @tables = NULL;
SELECT GROUP_CONCAT('\`', table_name, '\`') INTO @tables
FROM information_schema.tables
WHERE table_schema = DATABASE();

-- Vider toutes les tables
SET @drop_query = CONCAT('DROP TABLE IF EXISTS ', @tables);
PREPARE stmt FROM @drop_query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
SQLEOF

        echo "📥 Import des nouvelles données..."
        mysql -h "\$DB_HOST" -u "\$DB_USERNAME" -p"\$DB_PASSWORD" "\$DB_DATABASE" < storage/app/reset_import_$TIMESTAMP.sql

        echo "🗑️  Nettoyage du fichier temporaire..."
        rm storage/app/reset_import_$TIMESTAMP.sql

        echo "✅ Import terminé!"
EOF

    log_success "Import en production terminé!"
}

# Vérification post-import
verify_import() {
    log_step "Vérification de l'import"

    log_info "Statistiques PRODUCTION (après import):"
    ssh "$PRODUCTION_USER@$PRODUCTION_HOST" << EOF
        cd "$PRODUCTION_PATH"
        source .env

        mysql -h "\$DB_HOST" -u "\$DB_USERNAME" -p"\$DB_PASSWORD" "\$DB_DATABASE" -e "
            SELECT
                'Users' as 'Table',
                COUNT(*) as 'Lignes'
            FROM users
            UNION ALL
            SELECT 'Teams', COUNT(*) FROM teams
            UNION ALL
            SELECT 'Matches', COUNT(*) FROM matches
            UNION ALL
            SELECT 'Bars/PDV', COUNT(*) FROM bars
            UNION ALL
            SELECT 'Predictions', COUNT(*) FROM predictions
            UNION ALL
            SELECT 'Animations', COUNT(*) FROM animations;
        "
EOF
}

# Actions post-reset
post_reset_actions() {
    log_step "Actions post-reset"

    log_info "Nettoyage des caches Laravel sur Forge..."
    ssh "$PRODUCTION_USER@$PRODUCTION_HOST" << EOF
        cd "$PRODUCTION_PATH"

        echo "🧹 Clearing caches..."
        php artisan config:clear
        php artisan cache:clear
        php artisan view:clear
        php artisan route:clear

        echo "🔧 Optimizing..."
        php artisan optimize

        echo "✅ Caches cleared and optimized!"
EOF

    log_success "Caches nettoyés et application optimisée"
}

# Nettoyage local
cleanup() {
    log_step "Nettoyage local"

    if [ -d "$TEMP_DIR" ]; then
        rm -rf "$TEMP_DIR"
        log_success "Fichiers temporaires supprimés"
    fi
}

# Rapport final
show_summary() {
    log_header "✅ RESET FORGE TERMINÉ AVEC SUCCÈS"

    echo "📅 Date: $(date)"
    echo "🕒 Timestamp: $TIMESTAMP"
    echo ""
    echo "🌐 Production Forge:"
    echo "   URL: https://$PRODUCTION_HOST"
    echo "   Path: $PRODUCTION_PATH"
    echo ""
    echo "📦 Backups créés:"
    echo "   Sur Forge: $PRODUCTION_PATH/storage/backups/pre_reset_$TIMESTAMP.sql"
    echo "   Local: $BACKUP_DIR/forge_production_backup_$TIMESTAMP.sql"
    echo ""
    echo -e "${GREEN}✓ La base de données production est maintenant identique à votre base locale${NC}"
    echo ""
    echo "🔧 Actions recommandées:"
    echo "   1. Vérifier le site: https://$PRODUCTION_HOST"
    echo "   2. Tester les fonctionnalités critiques"
    echo "   3. Monitorer les logs si nécessaire"
    echo ""
    echo -e "${YELLOW}💡 Pour restaurer le backup en cas de problème:${NC}"
    echo "   ssh $PRODUCTION_USER@$PRODUCTION_HOST"
    echo "   cd $PRODUCTION_PATH"
    echo "   source .env"
    echo "   mysql -h \\\$DB_HOST -u \\\$DB_USERNAME -p\\\$DB_PASSWORD \\\$DB_DATABASE < storage/backups/pre_reset_$TIMESTAMP.sql"
    echo ""
}

# Fonction principale
main() {
    log_header "RESET PRODUCTION FORGE - SOBOA FOOT TIME"

    # Vérifications et avertissements
    show_warning
    check_requirements

    echo ""
    log_info "📊 Comparaison des données"
    echo ""

    show_local_stats
    show_production_stats

    echo ""
    log_warning "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    log_warning "Les données LOCALES vont remplacer les données PRODUCTION"
    log_warning "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""

    read -p "Confirmer le RESET COMPLET? (tapez 'RESET' en majuscules): " final_confirm

    if [ "$final_confirm" != "RESET" ]; then
        log_error "Opération annulée - confirmation incorrecte"
        exit 0
    fi

    # Exécution
    backup_production
    export_local_database
    import_to_production
    verify_import
    post_reset_actions
    cleanup
    show_summary
}

# Gestion des erreurs
trap 'log_error "Une erreur est survenue. Vérifiez les logs ci-dessus."' ERR

# Lancement
main "$@"
