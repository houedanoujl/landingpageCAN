# Quick Start - Reset Production

## 🚀 En 3 étapes

### Étape 1 : Préparation (une seule fois)

```bash
# Copier le fichier de configuration
cp .env.production.example .env.production

# Éditer avec vos vraies valeurs
nano .env.production  # ou utilisez votre éditeur préféré
```

Dans `.env.production`, remplissez :
- `DB_HOST` : adresse de votre serveur MySQL
- `DB_DATABASE` : nom de la base de données
- `DB_USERNAME` : utilisateur MySQL
- `DB_PASSWORD` : mot de passe

**OU** si vous utilisez SSH, vous pouvez sauter cette étape.

---

### Étape 2 : Tester la connexion

```bash
./test-production-connection.sh
```

Ce script va vérifier que :
- ✅ Votre Docker local est démarré
- ✅ La base locale est accessible
- ✅ La connexion à la production fonctionne

---

### Étape 3 : Reset de la production

```bash
./reset-production-database.sh
```

Le script va vous demander :
1. Confirmation que vous avez compris (tapez `oui`)
2. Confirmation finale (tapez `RESET`)
3. Méthode de connexion (SSH ou Direct)

**C'est tout!** 🎉

---

## ⚡ Aide rapide

### Si le test de connexion échoue

#### Docker n'est pas démarré
```bash
./vendor/bin/sail up -d
```

#### SSH échoue
```bash
# Testez manuellement
ssh forge@votre-serveur.com

# Si ça ne marche pas, vérifiez vos clés
ssh-add -l
```

#### Connexion MySQL échoue
```bash
# Testez manuellement
mysql -h DB_HOST -u DB_USER -p DB_NAME

# Vérifiez les credentials dans .env.production
cat .env.production
```

---

## 🆘 Restaurer un backup

Si quelque chose ne va pas après le reset :

### Via SSH :
```bash
ssh forge@soboa.com
cd /home/forge/soboa-foot-time
source .env
mysql -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < storage/backups/pre_reset_TIMESTAMP.sql
```

### Via connexion directe :
```bash
mysql -h DB_HOST -u DB_USER -p DB_NAME < storage/backups/production_backup_TIMESTAMP.sql
```

Le `TIMESTAMP` est affiché à la fin du script de reset.

---

## 📚 Documentation complète

Pour plus de détails, consultez :
- [RESET_PRODUCTION_GUIDE.md](./RESET_PRODUCTION_GUIDE.md) - Guide complet
- [README.md](./README.md#déploiement-et-gestion-de-la-base-de-données) - Section déploiement

---

## ⚠️ Rappel de sécurité

- Le script crée **TOUJOURS** un backup avant toute action
- Les backups sont dans `storage/backups/`
- Ne supprimez **JAMAIS** les backups manuellement
- En cas de doute, **restaurez le backup**
