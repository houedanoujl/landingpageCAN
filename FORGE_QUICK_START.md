# Quick Start Forge - Reset Production

## ⚡ 3 commandes seulement

### 1️⃣ Vérifier la configuration

```bash
cat .env.production
```

Si le fichier affiche vos credentials Forge ✅, passez à l'étape 2.

Sinon, il devrait déjà être créé. S'il manque, recréez-le :

```bash
cp .env.production.example .env.production
nano .env.production  # Ajustez si nécessaire
```

---

### 2️⃣ Tester la connexion (optionnel mais recommandé)

```bash
./test-production-connection.sh
```

Choisir option `1` (SSH), puis entrer :
- Serveur : `landingpagecan-qlrx6mvs.on-forge.com`
- User : `forge`

✅ Si tout est OK, passez à l'étape 3.

---

### 3️⃣ Reset de la production

```bash
./reset-production-forge.sh
```

Le script va demander :
1. **`oui`** - J'ai compris l'avertissement
2. **`RESET`** - Confirmation finale

Puis il fait tout automatiquement :
- Backup Forge
- Export local
- Upload
- Import
- Cache clear
- Vérification

**Durée** : 2-5 minutes selon la taille de la base

---

## 🎯 Ce qui se passe

```
1. Backup Forge     → storage/backups/pre_reset_TIMESTAMP.sql
2. Export local     → /tmp/local_full_export.sql
3. Upload SSH       → Forge:/storage/app/
4. DROP all tables  → Forge MySQL
5. Import           → Forge MySQL
6. Cache clear      → Laravel optimize
7. Verify           → Affiche stats
```

---

## ✅ C'est fait!

Vérifiez votre site :
👉 https://landingpagecan-qlrx6mvs.on-forge.com

---

## 🆘 Problème?

### Restaurer le backup

```bash
ssh forge@landingpagecan-qlrx6mvs.on-forge.com
cd /home/forge/landingpagecan-qlrx6mvs.on-forge.com/current
source .env
mysql -h $DB_HOST -u $DB_USERNAME -p$DB_PASSWORD $DB_DATABASE < storage/backups/pre_reset_TIMESTAMP.sql
```

Le `TIMESTAMP` est affiché à la fin du script de reset.

---

## 📚 Plus d'infos

- Guide complet : [FORGE_RESET_GUIDE.md](./FORGE_RESET_GUIDE.md)
- Troubleshooting : [RESET_PRODUCTION_GUIDE.md](./RESET_PRODUCTION_GUIDE.md)

---

**That's it!** 🎉
