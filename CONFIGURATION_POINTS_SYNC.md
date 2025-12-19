# 🔧 Configuration Points Sans Queue (Mode SYNC)

## 📝 Changement IMPORTANT dans le `.env`

Pour que les points soient calculés **immédiatement** sans avoir besoin de workers :

```env
QUEUE_CONNECTION=sync
```

Au lieu de :
```env
QUEUE_CONNECTION=database
# ou
QUEUE_CONNECTION=redis
```

## ✅ Avantages du Mode SYNC

- **Calcul immédiat** des points quand un match est terminé
- **Pas besoin de worker** ou supervisor
- **Pas de configuration** compliquée
- **Parfait pour** petites/moyennes applications

## ⚠️ Important : Après Changement

```bash
# Redémarrer l'application
docker-compose restart

# Ou si en production
php artisan config:clear
php artisan cache:clear
```

## 🎯 Résultat

Maintenant, quand vous :
- ✅ Terminez un match → Points calculés immédiatement
- ✅ Cliquez "Recalculer" → Points recalculés immédiatement
- ✅ Pas besoin de `php artisan queue:work`

---

Documentation créée le 19 décembre 2025
