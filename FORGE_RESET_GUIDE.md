# Guide Reset Production - Laravel Forge

## ⚡ Quick Start (3 étapes)

### Étape 1 : Vérifier la configuration

Le fichier `.env.production` a déjà été créé avec vos credentials Forge :

```bash
cat .env.production
```

Devrait afficher :
```
DB_HOST=127.0.0.1
DB_DATABASE=forge
DB_USERNAME=forge
DB_PASSWORD="eV9m8lxzrulTVNwAqgN0"

PRODUCTION_HOST=landingpagecan-qlrx6mvs.on-forge.com
PRODUCTION_USER=forge
PRODUCTION_PATH=/home/forge/landingpagecan-qlrx6mvs.on-forge.com/current
```

✅ **Fichier déjà dans .gitignore** - Ne sera jamais commité

---

### Étape 2 : Tester la connexion

```bash
# Test rapide
./test-production-connection.sh
```

**OU** test manuel :

```bash
# Test SSH vers Forge
ssh forge@landingpagecan-qlrx6mvs.on-forge.com

# Si connecté, vérifier l'app
cd /home/forge/landingpagecan-qlrx6mvs.on-forge.com/current
php artisan --version
```

---

### Étape 3 : Reset de la production

```bash
./reset-production-forge.sh
```

Le script va :
1. ✅ Vérifier Docker local et connexion SSH
2. ✅ Afficher stats LOCAL vs PRODUCTION
3. ✅ Demander confirmation `RESET`
4. ✅ Créer backup Forge (sur serveur + téléchargement local)
5. ✅ Exporter base locale
6. ✅ Uploader vers Forge
7. ✅ Importer (avec compte à rebours 5s)
8. ✅ Vérifier les données
9. ✅ Nettoyer les caches Laravel

---

## 📊 Ce qui se passe

### Avant le reset

**LOCAL (Docker Sail)** :
- Base : `can_soboa`
- User : `sail`
- Host : `mysql` (Docker)

**PRODUCTION (Forge)** :
- Base : `forge`
- User : `forge`
- Host : `127.0.0.1` (local MySQL sur serveur)

### Pendant le reset

```
┌─────────────┐
│ LOCAL DB    │  Docker Sail
│ can_soboa   │
└──────┬──────┘
       │
       │ mysqldump
       ▼
┌─────────────┐
│ Export.sql  │  Fichier temporaire
└──────┬──────┘
       │
       │ scp (SSH)
       ▼
┌─────────────┐
│ FORGE       │  Serveur de production
│ /storage/   │
└──────┬──────┘
       │
       │ mysql import
       ▼
┌─────────────┐
│ PROD DB     │  Base production
│ forge       │  ← ÉCRASÉE
└─────────────┘
```

---

## 🔒 Sécurités

### Backups automatiques

**Sur Forge** :
```
/home/forge/landingpagecan-qlrx6mvs.on-forge.com/current/storage/backups/
└── pre_reset_20251221_143022.sql
```

**En local** :
```
storage/backups/
└── forge_production_backup_20251221_143022.sql
```

### Confirmations requises

1. Question `oui/non` - Avertissement compris ?
2. Affichage stats LOCAL vs PROD
3. Question `RESET` - Confirmation finale
4. Compte à rebours 5 secondes - Dernière chance

---

## 🆘 Restaurer un backup

### Via SSH Forge

```bash
# Se connecter
ssh forge@landingpagecan-qlrx6mvs.on-forge.com

# Aller dans l'app
cd /home/forge/landingpagecan-qlrx6mvs.on-forge.com/current

# Charger les variables
source .env

# Restaurer
mysql -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < storage/backups/pre_reset_TIMESTAMP.sql
```

### Depuis votre machine locale

```bash
# Upload du backup local vers Forge
scp storage/backups/forge_production_backup_TIMESTAMP.sql \
    forge@landingpagecan-qlrx6mvs.on-forge.com:/home/forge/landingpagecan-qlrx6mvs.on-forge.com/current/storage/backups/

# Puis SSH et restore (voir ci-dessus)
```

Le `TIMESTAMP` est affiché à la fin du script.

---

## 🔧 Commandes utiles Forge

### Vérifier l'état de l'app

```bash
ssh forge@landingpagecan-qlrx6mvs.on-forge.com << 'EOF'
cd /home/forge/landingpagecan-qlrx6mvs.on-forge.com/current
php artisan about
EOF
```

### Statistiques de la base

```bash
ssh forge@landingpagecan-qlrx6mvs.on-forge.com << 'EOF'
cd /home/forge/landingpagecan-qlrx6mvs.on-forge.com/current
source .env
mysql -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE -e "
    SELECT 'Users' as 'Table', COUNT(*) as 'Count' FROM users
    UNION ALL SELECT 'Teams', COUNT(*) FROM teams
    UNION ALL SELECT 'Matches', COUNT(*) FROM matches
    UNION ALL SELECT 'Predictions', COUNT(*) FROM predictions;
"
EOF
```

### Nettoyer les caches

```bash
ssh forge@landingpagecan-qlrx6mvs.on-forge.com << 'EOF'
cd /home/forge/landingpagecan-qlrx6mvs.on-forge.com/current
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan optimize
EOF
```

### Voir les logs en direct

```bash
ssh forge@landingpagecan-qlrx6mvs.on-forge.com \
    "tail -f /home/forge/landingpagecan-qlrx6mvs.on-forge.com/current/storage/logs/laravel.log"
```

---

## ⚠️ Différences avec le déploiement normal

### Déploiement Forge standard
Votre script `forge-deployment-script.sh` :
- ✅ Pull du code Git
- ✅ `composer install`
- ✅ `npm run build`
- ✅ Migrations
- ✅ **Seeder SAFE** (préserve users)

### Reset complet
Le script `reset-production-forge.sh` :
- ❌ Pas de code Git
- ❌ Pas de build
- ✅ **ÉCRASE TOUT** (users inclus)
- ✅ Remplace par données locales

---

## 📋 Checklist avant reset

- [ ] Docker local démarré : `./vendor/bin/sail up -d`
- [ ] Données locales à jour
- [ ] Connexion SSH Forge testée
- [ ] `.env.production` vérifié
- [ ] Backup manuel supplémentaire (optionnel) :
  ```bash
  ssh forge@landingpagecan-qlrx6mvs.on-forge.com
  cd /home/forge/landingpagecan-qlrx6mvs.on-forge.com/current
  source .env
  mysqldump -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE > backup_manuel.sql
  ```

---

## 🎯 Workflow recommandé

### 1. Développement local
```bash
./vendor/bin/sail up -d
# Développer, tester, ajouter des données
```

### 2. Vérifier les données locales
```bash
./vendor/bin/sail artisan tinker
>>> User::count()
>>> Team::count()
>>> MatchGame::count()
```

### 3. Tester la connexion
```bash
./test-production-connection.sh
```

### 4. Reset production
```bash
./reset-production-forge.sh
```

### 5. Vérifier le site
```
https://landingpagecan-qlrx6mvs.on-forge.com
```

### 6. Nettoyer les caches (automatique dans le script)
Le script exécute déjà :
- `php artisan config:clear`
- `php artisan cache:clear`
- `php artisan view:clear`
- `php artisan route:clear`
- `php artisan optimize`

---

## 🚨 En cas de problème

### "SSH connection failed"

```bash
# Tester manuellement
ssh -v forge@landingpagecan-qlrx6mvs.on-forge.com

# Vérifier les clés SSH dans Forge Dashboard
# Settings > SSH Keys
```

### "Path does not exist"

Vérifier dans `.env.production` :
```bash
PRODUCTION_PATH=/home/forge/landingpagecan-qlrx6mvs.on-forge.com/current
```

Si le chemin est différent, le script vous le dira.

### "Docker not running"

```bash
./vendor/bin/sail up -d
```

### "MySQL access denied"

Les credentials dans `.env.production` sont extraits de votre Forge.
Si changés, mettez à jour `.env.production`.

---

## 📞 Support Forge

Dashboard Forge : https://forge.laravel.com/

Dans Forge, vous pouvez :
- Voir les logs
- Redémarrer les services
- Gérer la base de données
- Configurer les backups automatiques

---

## 📝 Notes importantes

1. **Le script utilise `current`** - C'est le symlink Forge vers la release active
2. **Pas besoin de redéployer** après le reset - les données sont directement modifiées
3. **Les backups ne sont pas supprimés** automatiquement - faites le ménage manuellement
4. **Le script nettoie les caches** - pas besoin de le faire manuellement
5. **Forge continue de fonctionner** pendant l'import (downtime minimal)

---

**Bon reset!** 🚀

En cas de problème, vous avez toujours le backup pour restaurer.
