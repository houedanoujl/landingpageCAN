#!/bin/bash

# ============================================================================
# SCRIPT DE RESET COMPLET DE LA BASE DE DONNÉES PRODUCTION
# ============================================================================
# Ce script ÉCRASE complètement la base de données production
# avec les données de votre environnement local
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

# Base de données locale (Docker Sail)
LOCAL_DB_HOST="mysql"
LOCAL_DB_PORT="3306"
LOCAL_DB_NAME="can_soboa"
LOCAL_DB_USER="sail"
LOCAL_DB_PASS="password"

# Configuration Production (à adapter selon votre environnement)
# Option 1: Si vous utilisez un fichier .env.production
ENV_PROD_FILE="$SCRIPT_DIR/.env.production"

# Option 2: SSH vers serveur distant
PRODUCTION_HOST=${PRODUCTION_HOST:-""}  # Ex: "soboa.com" ou IP
PRODUCTION_USER=${PRODUCTION_USER:-"forge"}
PRODUCTION_PATH=${PRODUCTION_PATH:-"/home/forge/soboa-foot-time"}

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
    echo -e "${RED}  1. ❌ SUPPRIMER toutes les données en production${NC}"
    echo -e "${RED}  2. 🔄 Les remplacer par vos données locales${NC}"
    echo -e "${RED}  3. ⚠️  Écraser: Users, Predictions, Teams, Matchs, etc.${NC}"
    echo ""
    echo -e "${GREEN}Protection:${NC}"
    echo -e "${GREEN}  ✓ Un backup de production sera créé avant toute action${NC}"
    echo -e "${GREEN}  ✓ Vous pourrez restaurer si nécessaire${NC}"
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

    # Vérifier Docker
    if ! docker compose ps | grep -q "laravel.test"; then
        log_error "Docker Compose n'est pas démarré. Lancez: ./vendor/bin/sail up -d"
        exit 1
    fi
    log_success "Docker Compose: OK"

    # Vérifier la base de données locale
    if ! docker compose exec -T mysql mysql -u "$LOCAL_DB_USER" -p"$LOCAL_DB_PASS" -e "USE $LOCAL_DB_NAME" &> /dev/null; then
        log_error "Impossible de se connecter à la base de données locale"
        exit 1
    fi
    log_success "Base de données locale: OK"

    # Créer les dossiers nécessaires
    mkdir -p "$BACKUP_DIR"
    mkdir -p "$TEMP_DIR"

    log_success "Prérequis validés"
}

# Afficher les statistiques locales
show_local_stats() {
    log_step "Statistiques de la base de données LOCALE"

    docker compose exec -T mysql mysql -u "$LOCAL_DB_USER" -p"$LOCAL_DB_PASS" "$LOCAL_DB_NAME" -e "
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
    "

    echo ""
    log_warning "Ces données vont REMPLACER celles de production!"
}

# Choisir la méthode de déploiement
choose_deployment_method() {
    log_step "Méthode de déploiement"

    echo "Comment accédez-vous à votre base de production?"
    echo ""
    echo "1. 📡 SSH vers un serveur distant (VPS, Forge, etc.)"
    echo "2. 🔗 Connexion directe (credentials dans .env.production)"
    echo "3. ❌ Annuler"
    echo ""
    read -p "Choisissez (1-3): " method_choice

    case $method_choice in
        1)
            DEPLOYMENT_METHOD="ssh"
            configure_ssh_deployment
            ;;
        2)
            DEPLOYMENT_METHOD="direct"
            configure_direct_deployment
            ;;
        3)
            log_info "Opération annulée"
            exit 0
            ;;
        *)
            log_error "Option invalide"
            exit 1
            ;;
    esac
}

# Configuration SSH
configure_ssh_deployment() {
    log_info "Configuration SSH"

    if [ -z "$PRODUCTION_HOST" ]; then
        read -p "Adresse du serveur (ex: soboa.com ou IP): " PRODUCTION_HOST
    fi

    if [ -z "$PRODUCTION_USER" ]; then
        read -p "Utilisateur SSH [forge]: " input_user
        PRODUCTION_USER=${input_user:-forge}
    fi

    if [ -z "$PRODUCTION_PATH" ]; then
        read -p "Chemin de l'application [/home/forge/soboa-foot-time]: " input_path
        PRODUCTION_PATH=${input_path:-/home/forge/soboa-foot-time}
    fi

    # Test de connexion
    log_info "Test de connexion SSH..."
    if ! ssh -o ConnectTimeout=10 "$PRODUCTION_USER@$PRODUCTION_HOST" "echo 'OK'" &> /dev/null; then
        log_error "Impossible de se connecter à $PRODUCTION_USER@$PRODUCTION_HOST"
        log_info "Vérifiez vos clés SSH ou vos credentials"
        exit 1
    fi

    log_success "Connexion SSH établie"
}

# Configuration connexion directe
configure_direct_deployment() {
    log_info "Configuration connexion directe"

    if [ ! -f "$ENV_PROD_FILE" ]; then
        log_error "Fichier .env.production introuvable"
        log_info "Créez un fichier .env.production avec vos credentials de production:"
        echo ""
        echo "DB_HOST=votre-host-production"
        echo "DB_PORT=3306"
        echo "DB_DATABASE=nom_base"
        echo "DB_USERNAME=user"
        echo "DB_PASSWORD=password"
        echo ""
        exit 1
    fi

    # Charger les variables
    source "$ENV_PROD_FILE"

    # Tester la connexion
    log_info "Test de connexion à la base production..."
    if ! mysql -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "USE $DB_DATABASE" &> /dev/null; then
        log_error "Impossible de se connecter à la base production"
        exit 1
    fi

    log_success "Connexion à la base production établie"
}

# Backup de la production
backup_production() {
    log_step "Sauvegarde de la base de données PRODUCTION"

    PROD_BACKUP="$BACKUP_DIR/production_backup_$TIMESTAMP.sql"

    if [ "$DEPLOYMENT_METHOD" == "ssh" ]; then
        log_info "Création du backup sur le serveur distant..."

        # Créer le backup sur le serveur
        ssh "$PRODUCTION_USER@$PRODUCTION_HOST" << EOF
            cd "$PRODUCTION_PATH"
            mkdir -p storage/backups

            # Récupérer les credentials depuis .env
            source .env

            # Créer le dump
            mysqldump -h "\$DB_HOST" -u "\$DB_USERNAME" -p"\$DB_PASSWORD" "\$DB_DATABASE" > "storage/backups/pre_reset_$TIMESTAMP.sql"

            echo "Backup créé: storage/backups/pre_reset_$TIMESTAMP.sql"
EOF

        # Télécharger le backup localement
        log_info "Téléchargement du backup en local..."
        scp "$PRODUCTION_USER@$PRODUCTION_HOST:$PRODUCTION_PATH/storage/backups/pre_reset_$TIMESTAMP.sql" "$PROD_BACKUP"

    else
        log_info "Création du backup production..."
        source "$ENV_PROD_FILE"
        mysqldump -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$PROD_BACKUP"
    fi

    # Vérifier la taille du backup
    BACKUP_SIZE=$(du -h "$PROD_BACKUP" | cut -f1)
    log_success "Backup production créé: $PROD_BACKUP ($BACKUP_SIZE)"

    echo ""
    log_info "En cas de problème, vous pourrez restaurer avec:"
    if [ "$DEPLOYMENT_METHOD" == "ssh" ]; then
        echo "  ssh $PRODUCTION_USER@$PRODUCTION_HOST 'cd $PRODUCTION_PATH && mysql < storage/backups/pre_reset_$TIMESTAMP.sql'"
    else
        echo "  mysql -h DB_HOST -u DB_USER -p DB_NAME < $PROD_BACKUP"
    fi
    echo ""
}

# Export de la base locale
export_local_database() {
    log_step "Export de la base de données LOCALE"

    LOCAL_EXPORT="$TEMP_DIR/local_full_export.sql"

    log_info "Création du dump MySQL..."
    docker compose exec -T mysql mysqldump \
        -u "$LOCAL_DB_USER" \
        -p"$LOCAL_DB_PASS" \
        "$LOCAL_DB_NAME" \
        --single-transaction \
        --quick \
        --lock-tables=false \
        > "$LOCAL_EXPORT"

    # Vérifier le fichier
    if [ ! -f "$LOCAL_EXPORT" ]; then
        log_error "Échec de l'export local"
        exit 1
    fi

    EXPORT_SIZE=$(du -h "$LOCAL_EXPORT" | cut -f1)
    log_success "Export local créé: $LOCAL_EXPORT ($EXPORT_SIZE)"
}

# Import en production
import_to_production() {
    log_step "Import des données en PRODUCTION"

    log_warning "Dernière chance d'annuler!"
    log_warning "La base de production va être ÉCRASÉE dans 5 secondes..."

    for i in 5 4 3 2 1; do
        echo -ne "\r${RED}[$i]${NC} Ctrl+C pour annuler..."
        sleep 1
    done
    echo ""

    LOCAL_EXPORT="$TEMP_DIR/local_full_export.sql"

    if [ "$DEPLOYMENT_METHOD" == "ssh" ]; then
        log_info "Upload du dump vers le serveur..."
        scp "$LOCAL_EXPORT" "$PRODUCTION_USER@$PRODUCTION_HOST:$PRODUCTION_PATH/storage/app/reset_import.sql"

        log_info "Import en cours sur le serveur distant..."
        ssh "$PRODUCTION_USER@$PRODUCTION_HOST" << EOF
            cd "$PRODUCTION_PATH"
            source .env

            echo "🗑️ Suppression de toutes les données..."
            mysql -h "\$DB_HOST" -u "\$DB_USERNAME" -p"\$DB_PASSWORD" "\$DB_DATABASE" -e "
                SET FOREIGN_KEY_CHECKS = 0;

                -- Lister toutes les tables
                SET @tables = NULL;
                SELECT GROUP_CONCAT('\`', table_name, '\`') INTO @tables
                FROM information_schema.tables
                WHERE table_schema = '\$DB_DATABASE';

                -- Vider toutes les tables
                SET @drop_query = CONCAT('DROP TABLE IF EXISTS ', @tables);
                PREPARE stmt FROM @drop_query;
                EXECUTE stmt;
                DEALLOCATE PREPARE stmt;

                SET FOREIGN_KEY_CHECKS = 1;
            "

            echo "📥 Import des nouvelles données..."
            mysql -h "\$DB_HOST" -u "\$DB_USERNAME" -p"\$DB_PASSWORD" "\$DB_DATABASE" < storage/app/reset_import.sql

            echo "🗑️ Nettoyage..."
            rm storage/app/reset_import.sql

            echo "✅ Import terminé!"
EOF

    else
        log_info "Import direct en production..."
        source "$ENV_PROD_FILE"

        log_info "Suppression de toutes les données..."
        mysql -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" << EOF
            SET FOREIGN_KEY_CHECKS = 0;

            -- Lister toutes les tables
            SET @tables = NULL;
            SELECT GROUP_CONCAT('\`', table_name, '\`') INTO @tables
            FROM information_schema.tables
            WHERE table_schema = '$DB_DATABASE';

            -- Vider toutes les tables
            SET @drop_query = CONCAT('DROP TABLE IF EXISTS ', @tables);
            PREPARE stmt FROM @drop_query;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;

            SET FOREIGN_KEY_CHECKS = 1;
EOF

        log_info "Import des nouvelles données..."
        mysql -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < "$LOCAL_EXPORT"
    fi

    log_success "Import en production terminé!"
}

# Vérifier les données importées
verify_import() {
    log_step "Vérification de l'import"

    if [ "$DEPLOYMENT_METHOD" == "ssh" ]; then
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
    else
        log_info "Statistiques PRODUCTION (après import):"
        source "$ENV_PROD_FILE"
        mysql -h "$DB_HOST" -P "${DB_PORT:-3306}" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" -e "
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
    fi
}

# Nettoyage
cleanup() {
    log_step "Nettoyage"

    if [ -d "$TEMP_DIR" ]; then
        rm -rf "$TEMP_DIR"
        log_success "Fichiers temporaires supprimés"
    fi
}

# Rapport final
show_summary() {
    log_header "✅ RESET TERMINÉ AVEC SUCCÈS"

    echo "📅 Date: $(date)"
    echo "🕒 Timestamp: $TIMESTAMP"
    echo ""
    echo "📦 Fichiers créés:"
    echo "   Backup production: $BACKUP_DIR/production_backup_$TIMESTAMP.sql"
    echo ""
    echo -e "${GREEN}✓ La base de données production est maintenant identique à votre base locale${NC}"
    echo ""

    if [ "$DEPLOYMENT_METHOD" == "ssh" ]; then
        echo "🔧 Actions recommandées:"
        echo "   1. Vérifier le site en production"
        echo "   2. Nettoyer le cache: ssh $PRODUCTION_USER@$PRODUCTION_HOST 'cd $PRODUCTION_PATH && php artisan cache:clear'"
        echo "   3. Monitorer les logs: ssh $PRODUCTION_USER@$PRODUCTION_HOST 'tail -f $PRODUCTION_PATH/storage/logs/laravel.log'"
    fi

    echo ""
    echo -e "${YELLOW}💡 Pour restaurer le backup en cas de problème:${NC}"
    if [ "$DEPLOYMENT_METHOD" == "ssh" ]; then
        echo "   ssh $PRODUCTION_USER@$PRODUCTION_HOST"
        echo "   cd $PRODUCTION_PATH"
        echo "   mysql < storage/backups/pre_reset_$TIMESTAMP.sql"
    else
        echo "   mysql -h DB_HOST -u DB_USER -p DB_NAME < $BACKUP_DIR/production_backup_$TIMESTAMP.sql"
    fi
}

# Fonction principale
main() {
    log_header "RESET COMPLET BASE DE DONNÉES PRODUCTION"

    # Étapes de sécurité
    show_warning
    check_requirements
    show_local_stats

    echo ""
    read -p "Confirmer le RESET COMPLET? (tapez 'RESET' en majuscules): " final_confirm

    if [ "$final_confirm" != "RESET" ]; then
        log_error "Opération annulée - confirmation incorrecte"
        exit 0
    fi

    # Configuration
    choose_deployment_method

    # Exécution
    backup_production
    export_local_database
    import_to_production
    verify_import
    cleanup
    show_summary
}

# Gestion des erreurs
trap 'log_error "Une erreur est survenue. Vérifiez les logs ci-dessus."' ERR

# Lancement
main "$@"
