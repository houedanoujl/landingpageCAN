# 🚀 Configuration Queue Worker en Production

## ✅ Bonne Nouvelle !

Votre fichier `deploy.sh` contient déjà `$RESTART_QUEUES()` à la ligne 22, ce qui signifie que **Forge gère automatiquement vos workers de queue** lors des déploiements.

## 🔧 Configuration Forge (Recommandée)

### 1. Vérifier le Daemon Queue sur Forge

**Connectez-vous à Laravel Forge** et allez dans votre serveur :

```
Serveur → Daemons → Vérifier qu'il y a un daemon pour queue:work
```

Le daemon devrait ressembler à :
```bash
Command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
Directory: /home/forge/votre-site.com/current
Processes: 1
User: forge
```

### 2. Si le Daemon N'existe Pas

Créez-le dans Forge :

**Paramètres :**
- **Command:** `php artisan queue:work database --sleep=3 --tries=3 --max-time=3600`
- **User:** `forge`
- **Directory:** `/home/forge/votre-domaine.com/current`
- **Processes:** `1` (ou plus si besoin)

### 3. Redémarrer le Daemon

Après chaque déploiement avec des changements de jobs :
```bash
# Via Forge UI
Daemons → Queue Worker → Restart

# Ou via SSH
php artisan queue:restart
```

## 🐳 Alternative : Supervisor (Si Pas Forge)

### 1. Installer Supervisor

```bash
sudo apt-get install supervisor
```

### 2. Créer la Configuration

Créez `/etc/supervisor/conf.d/laravel-worker.conf` :

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/landingpageCAN/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --timeout=60
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/landingpageCAN/storage/logs/worker.log
stopwaitsecs=3600
```

### 3. Démarrer le Worker

```bash
# Recharger la configuration
sudo supervisorctl reread
sudo supervisorctl update

# Démarrer le worker
sudo supervisorctl start laravel-worker:*

# Vérifier le statut
sudo supervisorctl status
```

### 4. Commandes Utiles

```bash
# Voir les logs
sudo supervisorctl tail -f laravel-worker:laravel-worker_00

# Redémarrer
sudo supervisorctl restart laravel-worker:*

# Arrêter
sudo supervisorctl stop laravel-worker:*
```

## 🔄 Redémarrage Après Déploiement

### Option A : Via Deploy Script (Déjà fait ✅)

Votre `deploy.sh` contient déjà :
```bash
$RESTART_QUEUES()
```

### Option B : Manuellement via SSH

```bash
# SSH dans le serveur
ssh forge@votre-serveur.com

# Aller dans le dossier du projet
cd /home/forge/votre-domaine.com/current

# Redémarrer la queue
php artisan queue:restart
```

### Option C : Automatique avec Envoyer (Laravel)

Si vous utilisez Laravel Envoyer, ajoutez dans les hooks :
```bash
php artisan queue:restart
```

## 📊 Monitoring de la Queue

### Vérifier que les Workers Fonctionnent

```bash
# Via SSH
ps aux | grep "queue:work"

# Vérifier les jobs en attente
php artisan queue:work database --once --verbose

# Voir les failed jobs
php artisan queue:failed
```

### Horizon (Alternative Avancée)

Si vous voulez un dashboard pour la queue :

1. **Installer Laravel Horizon**
```bash
composer require laravel/horizon
php artisan horizon:install
```

2. **Publier les assets**
```bash
php artisan horizon:publish
```

3. **Accéder au dashboard**
```
https://votre-domaine.com/horizon
```

## ⚠️ Configuration Critique

### Dans `.env` de Production

```env
# NE PAS METTRE sync EN PRODUCTION
QUEUE_CONNECTION=database

# Ou si vous avez Redis (recommandé)
QUEUE_CONNECTION=redis
```

### Pourquoi PAS `sync` en Production ?

- ❌ Bloque l'exécution (mauvaise UX)
- ❌ Timeout possible si le job est long
- ❌ Pas de retry en cas d'erreur
- ❌ Pas de parallélisation

## 🎯 Configuration Recommandée pour Votre Cas

### Pour `ProcessMatchPoints` Job

Étant donné que ce job peut prendre du temps (notifs WhatsApp, calculs multiples) :

```bash
# Worker configuration
php artisan queue:work database \
  --sleep=3 \
  --tries=3 \
  --max-time=3600 \
  --timeout=120 \
  --memory=256
```

**Explication :**
- `--sleep=3` : Attendre 3s entre chaque check de la queue
- `--tries=3` : Retry jusqu'à 3 fois si échec
- `--max-time=3600` : Redémarrer le worker après 1h
- `--timeout=120` : Timeout de 2 min par job
- `--memory=256` : Redémarrer si > 256 MB mémoire

## ✅ Checklist Production

- [ ] Daemon queue:work configuré dans Forge ou Supervisor
- [ ] `QUEUE_CONNECTION=database` (ou redis) dans `.env`
- [ ] `$RESTART_QUEUES()` dans `deploy.sh` ✅ (déjà fait)
- [ ] Test : dispatcher un job et vérifier qu'il s'exécute
- [ ] Logs configurés dans `storage/logs/`
- [ ] Monitoring en place (Horizon ou logs)

## 🧪 Test en Production

### 1. Dispatcher un Job Test

```bash
php artisan tinker
>>> \App\Jobs\ProcessMatchPoints::dispatch(14);
>>> exit
```

### 2. Vérifier l'Exécution

```bash
# Voir si le job est traité
tail -f storage/logs/laravel.log

# Ou dans la base de données
php artisan tinker
>>> DB::table('jobs')->count(); // Doit être 0 si traité
```

## 🚨 Dépannage

### Les Jobs Ne S'exécutent Pas ?

```bash
# 1. Vérifier que le worker tourne
ps aux | grep queue:work

# 2. Voir les jobs failed
php artisan queue:failed

# 3. Retry les failed jobs
php artisan queue:retry all

# 4. Redémarrer le worker
php artisan queue:restart
```

### Worker qui Crash ?

Vérifier les logs :
```bash
tail -f storage/logs/worker.log
tail -f storage/logs/laravel.log
```

## 📝 Résumé

**En production, vous avez 3 options :**

1. ✅ **Laravel Forge** (le plus simple) - Déjà configuré si vous utilisez Forge
2. ✅ **Supervisor** (manuel mais fiable) - Pour serveurs VPS classiques
3. ⚠️ **Systemd** (avancé) - Pour configurations personnalisées

**Votre `deploy.sh` est déjà prêt** avec `$RESTART_QUEUES()` !

---

**Documentation créée le 19 décembre 2025**
