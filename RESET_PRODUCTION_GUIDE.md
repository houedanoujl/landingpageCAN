# Guide de Reset de la Base de Données Production

## ⚠️ AVERTISSEMENT

Ce script **ÉCRASE COMPLÈTEMENT** la base de données de production avec vos données locales.

**Utilisez avec EXTRÊME PRÉCAUTION!**

---

## 🎯 Objectif

Le script `reset-production-database.sh` permet de :
- ✅ Sauvegarder automatiquement la base de production actuelle
- ✅ Exporter toutes vos données locales
- ✅ Importer ces données en production
- ✅ Vérifier que l'import s'est bien passé

---

## 📋 Prérequis

### 1. Docker doit être démarré
```bash
./vendor/bin/sail up -d
```

### 2. Configurer l'accès à la production

Vous avez **deux options** :

#### Option A : Connexion SSH (Recommandée)

Si votre serveur est accessible par SSH (VPS, Laravel Forge, etc.) :

```bash
# Testez d'abord votre connexion SSH
ssh forge@votre-serveur.com

# Si ça fonctionne, vous êtes prêt!
```

Le script vous demandera :
- Adresse du serveur (ex: `soboa.com` ou IP)
- Utilisateur SSH (ex: `forge`)
- Chemin de l'application (ex: `/home/forge/soboa-foot-time`)

#### Option B : Connexion directe à MySQL

Si vous pouvez vous connecter directement à la base MySQL de production :

1. Copiez le fichier exemple :
```bash
cp .env.production.example .env.production
```

2. Éditez `.env.production` avec vos vraies valeurs :
```env
DB_HOST=mysql.production.com
DB_PORT=3306
DB_DATABASE=soboa_production
DB_USERNAME=prod_user
DB_PASSWORD=VotreMo tDePasseSecurise
```

3. Testez la connexion :
```bash
mysql -h mysql.production.com -u prod_user -p soboa_production
```

---

## 🚀 Utilisation

### Lancer le script

```bash
./reset-production-database.sh
```

### Étapes du processus

1. **Avertissement de sécurité** - Vous devez taper `oui` pour continuer

2. **Vérification des prérequis** - Le script vérifie :
   - Docker est démarré
   - La base locale est accessible

3. **Statistiques locales** - Vous voyez combien de données seront copiées :
   ```
   Users: 150
   Teams: 32
   Matches: 64
   Predictions: 2500
   ```

4. **Confirmation finale** - Vous devez taper `RESET` en majuscules

5. **Choix de la méthode** :
   - Option 1 : SSH
   - Option 2 : Connexion directe

6. **Backup de production** - Sauvegarde automatique (vous pourrez restaurer!)

7. **Export local** - Création du dump MySQL

8. **Import en production** - Compte à rebours de 5 secondes (dernière chance d'annuler)

9. **Vérification** - Affichage des statistiques post-import

10. **Résumé** - Récapitulatif avec l'emplacement du backup

---

## 📊 Exemple d'exécution

```bash
$ ./reset-production-database.sh

╔════════════════════════════════════════════════════════╗
║   RESET COMPLET BASE DE DONNÉES PRODUCTION            ║
╚════════════════════════════════════════════════════════╝

⚠️  ATTENTION: Ce script va:
  1. ❌ SUPPRIMER toutes les données en production
  2. 🔄 Les remplacer par vos données locales
  3. ⚠️  Écraser: Users, Predictions, Teams, Matchs, etc.

Protection:
  ✓ Un backup de production sera créé avant toute action
  ✓ Vous pourrez restaurer si nécessaire

Avez-vous lu et compris cet avertissement? (oui/non): oui

▶ Vérification des prérequis
────────────────────────────────────────────────────────────
[✓] Docker Compose: OK
[✓] Base de données locale: OK
[✓] Prérequis validés

▶ Statistiques de la base de données LOCALE
────────────────────────────────────────────────────────────
Table          Lignes
Users          152
Teams          32
Matches        64
Bars/PDV       45
Predictions    2847
Animations     128
Point Logs     5624

⚠️  Ces données vont REMPLACER celles de production!

Confirmer le RESET COMPLET? (tapez 'RESET' en majuscules): RESET

Comment accédez-vous à votre base de production?

1. 📡 SSH vers un serveur distant (VPS, Forge, etc.)
2. 🔗 Connexion directe (credentials dans .env.production)
3. ❌ Annuler

Choisissez (1-3): 1

[INFO] Configuration SSH
Adresse du serveur (ex: soboa.com ou IP): soboa.com
Utilisateur SSH [forge]: forge
Chemin de l'application [/home/forge/soboa-foot-time]:
[INFO] Test de connexion SSH...
[✓] Connexion SSH établie

▶ Sauvegarde de la base de données PRODUCTION
────────────────────────────────────────────────────────────
[INFO] Création du backup sur le serveur distant...
[INFO] Téléchargement du backup en local...
[✓] Backup production créé: storage/backups/production_backup_20251221_143022.sql (15M)

[INFO] En cas de problème, vous pourrez restaurer avec:
  ssh forge@soboa.com 'cd /home/forge/soboa-foot-time && mysql < storage/backups/pre_reset_20251221_143022.sql'

▶ Export de la base de données LOCALE
────────────────────────────────────────────────────────────
[INFO] Création du dump MySQL...
[✓] Export local créé: /tmp/db_reset_20251221_143022/local_full_export.sql (12M)

▶ Import des données en PRODUCTION
────────────────────────────────────────────────────────────
[⚠] Dernière chance d'annuler!
[⚠] La base de production va être ÉCRASÉE dans 5 secondes...
[5] Ctrl+C pour annuler...
[4] Ctrl+C pour annuler...
[3] Ctrl+C pour annuler...
[2] Ctrl+C pour annuler...
[1] Ctrl+C pour annuler...

[INFO] Upload du dump vers le serveur...
[INFO] Import en cours sur le serveur distant...
[✓] Import en production terminé!

▶ Vérification de l'import
────────────────────────────────────────────────────────────
[INFO] Statistiques PRODUCTION (après import):
Table          Lignes
Users          152
Teams          32
Matches        64
Bars/PDV       45
Predictions    2847
Animations     128

▶ Nettoyage
────────────────────────────────────────────────────────────
[✓] Fichiers temporaires supprimés

╔════════════════════════════════════════════════════════╗
║   ✅ RESET TERMINÉ AVEC SUCCÈS                         ║
╚════════════════════════════════════════════════════════╝

📅 Date: Sat Dec 21 14:30:45 UTC 2024
🕒 Timestamp: 20251221_143022

📦 Fichiers créés:
   Backup production: storage/backups/production_backup_20251221_143022.sql

✓ La base de données production est maintenant identique à votre base locale

🔧 Actions recommandées:
   1. Vérifier le site en production
   2. Nettoyer le cache: ssh forge@soboa.com 'cd /home/forge/soboa-foot-time && php artisan cache:clear'
   3. Monitorer les logs: ssh forge@soboa.com 'tail -f /home/forge/soboa-foot-time/storage/logs/laravel.log'

💡 Pour restaurer le backup en cas de problème:
   ssh forge@soboa.com
   cd /home/forge/soboa-foot-time
   mysql < storage/backups/pre_reset_20251221_143022.sql
```

---

## 🆘 En cas de problème

### Restaurer le backup de production

Si quelque chose ne va pas, vous pouvez restaurer le backup :

#### Via SSH :
```bash
ssh forge@soboa.com
cd /home/forge/soboa-foot-time
source .env
mysql -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < storage/backups/pre_reset_TIMESTAMP.sql
```

#### Via connexion directe :
```bash
mysql -h DB_HOST -u DB_USER -p DB_NAME < storage/backups/production_backup_TIMESTAMP.sql
```

### Erreurs courantes

#### "Docker Compose n'est pas démarré"
```bash
./vendor/bin/sail up -d
```

#### "Impossible de se connecter à la base locale"
```bash
# Vérifier que MySQL est bien démarré
docker compose ps
```

#### "Connexion SSH échouée"
```bash
# Tester votre connexion
ssh -v forge@votre-serveur.com

# Vérifier vos clés SSH
ssh-add -l
```

#### "Accès refusé à la base MySQL"
```bash
# Vérifier les credentials dans .env.production
cat .env.production

# Tester la connexion
mysql -h HOST -u USER -p DATABASE
```

---

## 🔒 Sécurité

### Le script :
- ✅ Crée **toujours** un backup avant toute action
- ✅ Demande **plusieurs confirmations**
- ✅ Affiche un **compte à rebours** avant l'action critique
- ✅ Sauvegarde les backups localement ET sur le serveur
- ✅ Ne supprime **jamais** les backups automatiquement

### Fichiers sensibles :
```bash
# Ajouter à .gitignore
echo ".env.production" >> .gitignore
```

⚠️ **Ne commitez JAMAIS .env.production dans Git!**

---

## 📁 Structure des backups

Les backups sont sauvegardés dans :
```
storage/backups/
├── production_backup_20251221_143022.sql    # Backup téléchargé localement
└── local_backup_TIMESTAMP.sql               # Vos exports locaux
```

Sur le serveur de production :
```
/home/forge/soboa-foot-time/storage/backups/
└── pre_reset_20251221_143022.sql            # Backup avant reset
```

---

## 🔄 Workflow recommandé

1. **Testez d'abord en staging** (si vous avez un environnement de staging)

2. **Faites un backup manuel supplémentaire** :
```bash
./sync-database.sh
# Choisir option 2: Backup production uniquement
```

3. **Vérifiez vos données locales** :
```bash
./vendor/bin/sail artisan tinker
>>> User::count()
>>> Team::count()
>>> MatchGame::count()
```

4. **Lancez le script de reset** :
```bash
./reset-production-database.sh
```

5. **Vérifiez le site en production**

6. **Nettoyez le cache Laravel** :
```bash
ssh forge@soboa.com 'cd /home/forge/soboa-foot-time && php artisan cache:clear'
```

---

## 📞 Support

Si vous rencontrez des problèmes :

1. Consultez la section "En cas de problème" ci-dessus
2. Vérifiez les logs du script
3. Gardez les backups - ne les supprimez pas!
4. En cas de doute, **restaurez le backup**

---

## 📝 Notes

- Les backups sont horodatés avec un timestamp unique
- Le script ne supprime jamais les anciens backups (faites le ménage manuellement)
- La connexion SSH est testée avant toute action
- Le script s'arrête immédiatement en cas d'erreur (`set -e`)

---

**Bon déploiement!** 🚀
