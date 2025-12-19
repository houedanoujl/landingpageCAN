#!/bin/bash

# ============================================================================
# SCRIPT DE DÉPLOIEMENT PRODUCTION - SOBOA FOOT TIME
# ============================================================================
# Ce script déploie l'application depuis le local vers la production
# incluant le code et la base de données
# ============================================================================

set -e  # Arrêter en cas d'erreur

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="$SCRIPT_DIR/storage/backups"

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m'

# Configuration Production (à adapter)
PRODUCTION_HOST=${PRODUCTION_HOST:-"your-server.com"}
PRODUCTION_USER=${PRODUCTION_USER:-"forge"}
PRODUCTION_PATH=${PRODUCTION_PATH:-"/home/forge/soboa-foot-time"}
PRODUCTION_BRANCH=${PRODUCTION_BRANCH:-"main"}

# Fonctions utilitaires
log_header() {
    echo ""
    echo -e "${MAGENTA}╔════════════════════════════════════════╗${NC}"
    echo -e "${MAGENTA}║   $1${NC}"
    echo -e "${MAGENTA}╚════════════════════════════════════════╝${NC}"
    echo ""
}

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

log_error() {
    echo -e "${RED}[✗]${NC} $1"
}

log_step() {
    echo ""
    echo -e "${CYAN}▶ $1${NC}"
    echo -e "${CYAN}$( printf '%.0s─' {1..40} )${NC}"
}

# Vérifier les prérequis
check_requirements() {
    log_step "Vérification des prérequis"
    
    # Vérifier Git
    if ! command -v git &> /dev/null; then
        log_error "Git n'est pas installé"
        exit 1
    fi
    log_success "Git: OK"
    
    # Vérifier Docker
    if ! docker compose version &> /dev/null; then
        log_warning "Docker Compose n'est pas disponible"
    else
        log_success "Docker Compose: OK"
    fi
    
    # Vérifier SSH
    if ! command -v ssh &> /dev/null; then
        log_error "SSH n'est pas installé"
        exit 1
    fi
    log_success "SSH: OK"
    
    # Vérifier la connexion SSH
    log_info "Test de connexion SSH..."
    if ssh -o ConnectTimeout=5 "$PRODUCTION_USER@$PRODUCTION_HOST" "echo 'Connection OK'" &> /dev/null; then
        log_success "Connexion SSH: OK"
    else
        log_error "Impossible de se connecter à $PRODUCTION_USER@$PRODUCTION_HOST"
        log_info "Vérifiez votre configuration SSH"
        exit 1
    fi
}

# Créer un backup local
backup_local() {
    log_step "Backup de la base de données locale"
    
    mkdir -p "$BACKUP_DIR"
    
    if docker compose ps | grep -q "laravel.test"; then
        log_info "Backup via Docker..."
        docker compose exec -T mysql mysqldump -u root -ppassword soboa_foot_time > "$BACKUP_DIR/local_backup_$TIMESTAMP.sql"
    else
        log_info "Backup via MySQL local..."
        mysqldump -h 127.0.0.1 -P 3306 -u root -ppassword soboa_foot_time > "$BACKUP_DIR/local_backup_$TIMESTAMP.sql"
    fi
    
    log_success "Backup créé: local_backup_$TIMESTAMP.sql"
}

# Créer un backup production
backup_production() {
    log_step "Backup de la base de données production"
    
    log_info "Création du backup sur le serveur..."
    ssh "$PRODUCTION_USER@$PRODUCTION_HOST" "cd $PRODUCTION_PATH && php artisan db:backup" || true
    
    log_success "Backup production créé"
}

# Pousser le code vers Git
push_code() {
    log_step "Push du code vers Git"
    
    # Vérifier l'état Git
    if [[ -n $(git status -s) ]]; then
        log_warning "Des modifications non commitées détectées"
        
        echo "Fichiers modifiés:"
        git status -s
        echo ""
        
        read -p "Voulez-vous commiter ces changements? (oui/non): " commit_changes
        
        if [ "$commit_changes" == "oui" ]; then
            read -p "Message de commit: " commit_msg
            git add .
            git commit -m "$commit_msg"
        else
            log_warning "Continuez avec le code existant sur Git"
        fi
    fi
    
    # Push vers origin
    log_info "Push vers origin/$PRODUCTION_BRANCH..."
    git push origin "$PRODUCTION_BRANCH"
    
    log_success "Code poussé vers Git"
}

# Déployer sur production
deploy_production() {
    log_step "Déploiement sur le serveur de production"
    
    log_info "Connexion au serveur..."
    
    ssh "$PRODUCTION_USER@$PRODUCTION_HOST" << EOF
        set -e
        
        echo "📍 Déploiement dans: $PRODUCTION_PATH"
        cd "$PRODUCTION_PATH"
        
        echo "🔄 Pull des dernières modifications..."
        git pull origin "$PRODUCTION_BRANCH"
        
        echo "📦 Installation des dépendances Composer..."
        composer install --no-dev --optimize-autoloader
        
        echo "📦 Installation des dépendances NPM..."
        npm ci
        
        echo "🏗️ Build des assets..."
        npm run build
        
        echo "🗄️ Exécution des migrations..."
        php artisan migrate --force
        
        echo "🔧 Optimisation de l'application..."
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        php artisan event:cache
        
        echo "🗑️ Nettoyage du cache..."
        php artisan cache:clear
        
        echo "🔄 Redémarrage des queues..."
        php artisan queue:restart || true
        
        echo "✅ Déploiement terminé!"
EOF
    
    log_success "Déploiement production terminé"
}

# Synchroniser la base de données
sync_database() {
    log_step "Synchronisation de la base de données"
    
    echo "Options de synchronisation:"
    echo "1. 🔄 Sync COMPLÈTE (écrase tout)"
    echo "2. 🛡️ Sync SÉCURISÉE (préserve users et predictions)"
    echo "3. 📊 Sync DONNÉES uniquement (teams, matchs, PDV)"
    echo "4. ⏭️ Passer cette étape"
    echo ""
    read -p "Choisissez une option (1-4): " sync_choice
    
    case $sync_choice in
        1)
            sync_database_full
            ;;
        2)
            sync_database_safe
            ;;
        3)
            sync_database_data
            ;;
        4)
            log_info "Synchronisation de base de données ignorée"
            ;;
        *)
            log_error "Option invalide"
            ;;
    esac
}

# Sync complète de la BD
sync_database_full() {
    log_warning "⚠️ ATTENTION: Cette action va ÉCRASER toute la base de production!"
    read -p "Êtes-vous VRAIMENT sûr? (tapez 'CONFIRMER'): " confirmation
    
    if [ "$confirmation" != "CONFIRMER" ]; then
        log_info "Synchronisation annulée"
        return
    fi
    
    # Export local
    log_info "Export de la base locale..."
    LOCAL_EXPORT="$BACKUP_DIR/full_export_$TIMESTAMP.sql"
    
    if docker compose ps | grep -q "laravel.test"; then
        docker compose exec -T mysql mysqldump -u root -ppassword soboa_foot_time > "$LOCAL_EXPORT"
    else
        mysqldump -h 127.0.0.1 -P 3306 -u root -ppassword soboa_foot_time > "$LOCAL_EXPORT"
    fi
    
    # Upload vers production
    log_info "Upload du dump vers production..."
    scp "$LOCAL_EXPORT" "$PRODUCTION_USER@$PRODUCTION_HOST:$PRODUCTION_PATH/storage/app/"
    
    # Import en production
    log_info "Import en production..."
    ssh "$PRODUCTION_USER@$PRODUCTION_HOST" "cd $PRODUCTION_PATH && php artisan db:restore --file=full_export_$TIMESTAMP.sql --force"
    
    log_success "Base de données synchronisée (complète)"
}

# Sync sécurisée de la BD
sync_database_safe() {
    log_info "Synchronisation sécurisée (préserve les utilisateurs)..."
    
    # Export local sans les tables utilisateurs
    LOCAL_EXPORT="$BACKUP_DIR/safe_export_$TIMESTAMP.sql"
    
    if docker compose ps | grep -q "laravel.test"; then
        docker compose exec -T mysql mysqldump -u root -ppassword soboa_foot_time \
            --ignore-table=soboa_foot_time.users \
            --ignore-table=soboa_foot_time.predictions \
            --ignore-table=soboa_foot_time.point_logs \
            --ignore-table=soboa_foot_time.password_reset_tokens \
            --ignore-table=soboa_foot_time.personal_access_tokens \
            --ignore-table=soboa_foot_time.sessions \
            > "$LOCAL_EXPORT"
    fi
    
    # Upload vers production
    log_info "Upload du dump vers production..."
    scp "$LOCAL_EXPORT" "$PRODUCTION_USER@$PRODUCTION_HOST:$PRODUCTION_PATH/storage/app/"
    
    # Import en production
    log_info "Import en production (données uniquement)..."
    ssh "$PRODUCTION_USER@$PRODUCTION_HOST" << EOF
        cd "$PRODUCTION_PATH"
        
        # Nettoyer les tables de planning
        php artisan tinker --execute="
            DB::table('animations')->truncate();
            DB::table('matches')->truncate();
            DB::table('bars')->truncate();
            DB::table('teams')->truncate();
        "
        
        # Importer les nouvelles données
        mysql -u \$DB_USERNAME -p\$DB_PASSWORD \$DB_DATABASE < storage/app/safe_export_$TIMESTAMP.sql
        
        # Nettoyer
        rm storage/app/safe_export_$TIMESTAMP.sql
EOF
    
    log_success "Base de données synchronisée (mode sécurisé)"
}

# Sync via seeder
sync_database_data() {
    log_info "Synchronisation via seeder Laravel..."
    
    # Exporter les données locales
    log_info "Export des données locales..."
    docker compose exec laravel.test bash -c "cd /app && php artisan db:seed --class=ProductionSyncSeeder --export"
    
    # Copier le fichier d'export
    cp "$SCRIPT_DIR/storage/app/production_sync.json" "$BACKUP_DIR/production_sync_$TIMESTAMP.json"
    
    # Upload vers production
    log_info "Upload des données vers production..."
    scp "$SCRIPT_DIR/storage/app/production_sync.json" "$PRODUCTION_USER@$PRODUCTION_HOST:$PRODUCTION_PATH/storage/app/"
    
    # Import en production
    log_info "Import en production via seeder..."
    ssh "$PRODUCTION_USER@$PRODUCTION_HOST" "cd $PRODUCTION_PATH && php artisan db:seed --class=ProductionSyncSeeder --import --force"
    
    log_success "Données synchronisées via seeder"
}

# Tests post-déploiement
run_tests() {
    log_step "Tests post-déploiement"
    
    log_info "Vérification du site..."
    
    # Test HTTP
    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "https://$PRODUCTION_HOST")
    
    if [ "$HTTP_STATUS" == "200" ]; then
        log_success "Site accessible (HTTP $HTTP_STATUS)"
    else
        log_error "Site non accessible (HTTP $HTTP_STATUS)"
    fi
    
    # Test de l'API
    API_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "https://$PRODUCTION_HOST/api/health")
    
    if [ "$API_STATUS" == "200" ]; then
        log_success "API fonctionnelle"
    else
        log_warning "API non testable"
    fi
    
    # Statistiques production
    log_info "Statistiques production:"
    ssh "$PRODUCTION_USER@$PRODUCTION_HOST" "cd $PRODUCTION_PATH && php artisan tinker --execute='
        echo \"Users: \" . \\App\\Models\\User::count();
        echo \" | Teams: \" . \\App\\Models\\Team::count();
        echo \" | Matches: \" . \\App\\Models\\MatchGame::count();
        echo \" | Predictions: \" . \\App\\Models\\Prediction::count();
    '"
}

# Rapport final
show_summary() {
    log_header "RÉSUMÉ DU DÉPLOIEMENT"
    
    echo "📅 Date: $(date)"
    echo "🏷️ Timestamp: $TIMESTAMP"
    echo "🌐 Serveur: $PRODUCTION_HOST"
    echo "📁 Chemin: $PRODUCTION_PATH"
    echo "🌿 Branche: $PRODUCTION_BRANCH"
    echo ""
    
    if [ -f "$BACKUP_DIR/local_backup_$TIMESTAMP.sql" ]; then
        echo "📦 Backups créés:"
        echo "   - local_backup_$TIMESTAMP.sql"
    fi
    
    echo ""
    echo -e "${GREEN}✅ Déploiement terminé avec succès!${NC}"
    echo ""
    echo "📋 Actions recommandées:"
    echo "   1. Vérifier le site: https://$PRODUCTION_HOST"
    echo "   2. Monitorer les logs: ssh $PRODUCTION_USER@$PRODUCTION_HOST 'tail -f $PRODUCTION_PATH/storage/logs/laravel.log'"
    echo "   3. Vérifier les queues: ssh $PRODUCTION_USER@$PRODUCTION_HOST 'cd $PRODUCTION_PATH && php artisan queue:listen'"
}

# Menu principal
main() {
    log_header "DÉPLOIEMENT PRODUCTION - SOBOA FOOT TIME"
    
    echo "Ce script va déployer l'application en production."
    echo "Assurez-vous d'avoir:"
    echo "  ✓ Accès SSH au serveur de production"
    echo "  ✓ Les dernières modifications commitées"
    echo "  ✓ Un backup récent de la production"
    echo ""
    
    read -p "Voulez-vous continuer? (oui/non): " continue_deploy
    
    if [ "$continue_deploy" != "oui" ]; then
        log_info "Déploiement annulé"
        exit 0
    fi
    
    # Exécution des étapes
    check_requirements
    backup_local
    backup_production
    push_code
    deploy_production
    sync_database
    run_tests
    show_summary
}

# Lancer le script
main
