# Guide de déploiement - CAN SOBOA 2025

## ⚡ Optimisations pour Production (Laravel Forge)

### 1. Scripts de déploiement Forge

Ajoutez ces commandes dans votre script de déploiement Laravel Forge :

```bash
cd /home/forge/your-domain.com
git pull origin main

# Installation des dépendances
composer install --no-dev --optimize-autoloader

# Build des assets
npm ci
npm run build

# Optimisations Laravel (CRITIQUE pour performance)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Migrations
php artisan migrate --force

# Redémarrage
php artisan queue:restart
php artisan opcache:clear  # Si opcache est installé
```

### 2. Configuration Redis (Cache & Sessions)

Dans votre `.env` de production :

```env
# Cache Configuration
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Database Connection Pooling
DB_CONNECTION=mysql
DB_POOL_MIN=2
DB_POOL_MAX=10
```

### 3. Optimisations PHP (php.ini)

Demandez à Laravel Forge d'activer ces paramètres :

```ini
; OPcache (CRITIQUE)
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0  ; En production uniquement
opcache.revalidate_freq=0
opcache.fast_shutdown=1

; Performance
max_execution_time=300
memory_limit=512M
post_max_size=50M
upload_max_filesize=50M

; Realpath cache
realpath_cache_size=4096K
realpath_cache_ttl=600
```

### 4. Configuration Nginx (Laravel Forge)

Activez la compression et le cache statique :

```nginx
# Gzip compression
gzip on;
gzip_vary on;
gzip_proxied any;
gzip_comp_level 6;
gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

# Brotli (si disponible)
brotli on;
brotli_comp_level 6;
brotli_types text/xml image/svg+xml application/x-font-ttf image/vnd.microsoft.icon application/x-font-opentype application/json font/eot application/vnd.ms-fontobject application/javascript font/otf application/xml application/xhtml+xml text/javascript  application/x-javascript text/plain application/x-font-truetype application/xml+rss image/x-icon font/opentype text/css image/x-win-bitmap;

# Cache statique (CSS, JS, Images)
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
}

# Cache HTML
location ~* \.html$ {
    expires 1h;
    add_header Cache-Control "public, must-revalidate";
}
```

### 5. CDN (Cloudflare recommandé)

1. Créez un compte Cloudflare
2. Ajoutez votre domaine
3. Activez ces options :
   - Auto Minify (CSS, JS, HTML)
   - Brotli compression
   - Rocket Loader
   - Polish (optimisation images)
   - Argo Smart Routing
   - HTTP/2 et HTTP/3

### 6. Optimisation Base de Données

```sql
-- Index importants (ajoutez ces migrations)
CREATE INDEX idx_matches_status_date ON matches(status, match_date);
CREATE INDEX idx_predictions_user_match ON predictions(user_id, match_id);
CREATE INDEX idx_predictions_match_points ON predictions(match_id, points_earned);
CREATE INDEX idx_users_points ON users(points_total DESC);
CREATE INDEX idx_bars_active_location ON bars(is_active, latitude, longitude);
```

### 7. Monitoring et Alertes

Installez Laravel Telescope en développement uniquement :

```bash
# Development only
composer require laravel/telescope --dev
```

Pour la production, utilisez :
- **Laravel Pulse** (monitoring en temps réel)
- **Sentry** (tracking des erreurs)
- **New Relic** ou **Blackfire.io** (profiling performance)

### 8. Commandes de maintenance

```bash
# Vider tous les caches
php artisan optimize:clear

# Optimiser tout pour production
php artisan optimize

# Nettoyer les sessions expirées (cron journalier)
php artisan session:gc

# Nettoyer le cache expiré
php artisan cache:prune-stale-tags
```

### 9. Configuration des Queues (Workers)

Sur Laravel Forge, configurez un worker pour les queues :

```bash
# Dans Forge > Workers
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --timeout=60
```

Nombre de workers recommandé : 2-3 pour commencer, augmentez selon la charge.

### 10. Variables d'environnement production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# Logs
LOG_CHANNEL=stack
LOG_LEVEL=error  # warning en production

# Performance
QUEUE_CONNECTION=redis
CACHE_STORE=redis
SESSION_DRIVER=redis

# Ne pas inclure VITE_DEV_SERVER_URL en production !
# VITE_DEV_SERVER_URL=  # Commenté ou supprimé
```

## 🔥 Checklist avant le lancement

- [ ] Tests de charge avec Apache Bench ou k6
- [ ] Backup automatique de la base de données
- [ ] SSL/HTTPS configuré (Let's Encrypt via Forge)
- [ ] Monitoring actif (Sentry, Pulse)
- [ ] Rate limiting configuré
- [ ] Headers de sécurité activés
- [ ] CDN Cloudflare configuré
- [ ] Caches Laravel activés
- [ ] OPcache PHP activé
- [ ] Workers de queue en cours d'exécution
- [ ] Plan de scalabilité (augmenter CPU/RAM si besoin)

## 📊 Capacité attendue

Avec ces optimisations :
- **Sans CDN** : ~500-1000 utilisateurs simultanés
- **Avec CDN** : ~5000-10000+ utilisateurs simultanés
- **Temps de réponse** : < 200ms (pages, API)
- **Assets statiques** : < 50ms via CDN

## 🚨 Plan de secours en cas de surcharge

1. Activez "Under Attack Mode" sur Cloudflare
2. Augmentez les workers de queue à 5-10
3. Augmentez la RAM du serveur (2GB → 4GB → 8GB)
4. Activez le mode maintenance avec page statique si nécessaire
