# 📅 Guide d'Affichage des Matchs

## 🎯 Règles d'Affichage

### Page d'Accueil (`/`) et Page Matchs (`/matches`)

#### ✅ Matchs Affichés
1. **Matchs futurs uniquement** : `match_date >= maintenant`
2. **Status non terminé** : `status != 'finished'`
3. **Phase de poule** : Toujours affichée
4. **Phases finales** : Affichées uniquement à partir de **J-1** du premier match de cette phase

#### ❌ Matchs NON Affichés
- ❌ Matchs passés (date dépassée)
- ❌ Matchs terminés (status = 'finished')
- ❌ Phases finales futures (avant J-1 de leur date)

## 📊 Exemples Concrets

### Phase de Poule
```
✅ Toujours visible (tant que dates futures)
```

### 1/8e de Finale
```
Premier 1/8e : 3 janvier 2026 à 16h
→ Affichage : À partir du 2 janvier 2026 (J-1)
→ Avant le 2 janvier : ❌ Invisible
```

### Quarts de Finale
```
Premier quart : 9 janvier 2026 à 16h
→ Affichage : À partir du 8 janvier 2026 (J-1)
→ Avant le 8 janvier : ❌ Invisible
```

### Demi-Finales
```
Première demi : 14 janvier 2026 à 16h
→ Affichage : À partir du 13 janvier 2026 (J-1)
→ Avant le 13 janvier : ❌ Invisible
```

### Finale
```
Finale : 18 janvier 2026 à 16h
→ Affichage : À partir du 17 janvier 2026 (J-1)
→ Avant le 17 janvier : ❌ Invisible
```

## 🔧 Logique Technique

### Page d'Accueil (`HomeController@index`)
```php
// 1. Récupérer tous les matchs futurs
$allUpcomingMatches = MatchGame::where('status', '!=', 'finished')
    ->where('match_date', '>=', now())
    ->get();

// 2. Filtrer par phase
$upcomingMatches = $allUpcomingMatches->filter(function ($match) {
    // Phase de poule : toujours visible
    if ($match->phase === 'group_stage') return true;
    
    // Phases finales : visible si J-1 du 1er match de la phase
    $firstMatchOfPhase = ...->sortBy('match_date')->first();
    return now() >= $firstMatchOfPhase->match_date->subDay();
});
```

### Page Matchs (`HomeController@matches`)
```php
// Même logique que la page d'accueil
```

## 📱 Affichage sur les Pages

### Page d'Accueil
- **Section "Prochains Matchs"**
- Grille responsive : 1→2→3→4 colonnes
- TOUS les matchs éligibles (pas de limite)

### Page /matches
- **Groupé par phase** : Phase de poule, 1/8e, Quarts, etc.
- **Phase de poule groupée par groupe** : A, B, C, D...
- Affichage des pronostics utilisateur

## ⏰ Calendrier des Phases

### Phase de Poule
- 📅 Dates : ~21-31 décembre 2025
- ✅ Visible : Dès maintenant

### 1/8e de Finale
- 📅 Premier match : ~3 janvier 2026
- ✅ Visible : À partir du 2 janvier 2026

### Quarts de Finale
- 📅 Premier match : ~9 janvier 2026
- ✅ Visible : À partir du 8 janvier 2026

### Demi-Finales
- 📅 Premier match : ~14 janvier 2026
- ✅ Visible : À partir du 13 janvier 2026

### Finale
- 📅 Match : ~18 janvier 2026
- ✅ Visible : À partir du 17 janvier 2026

## 🎬 Scénarios Utilisateur

### Scénario 1 : 25 décembre 2025
```
✅ Phase de poule visible (matchs en cours)
❌ 1/8e de finale invisible (trop tôt)
❌ Quarts invisible (trop tôt)
❌ Demi-finales invisible (trop tôt)
❌ Finale invisible (trop tôt)
```

### Scénario 2 : 2 janvier 2026
```
✅ Phase de poule visible (si matchs restants)
✅ 1/8e de finale visible (J-1 du premier 1/8e)
❌ Quarts invisible (trop tôt)
❌ Demi-finales invisible (trop tôt)
❌ Finale invisible (trop tôt)
```

### Scénario 3 : 17 janvier 2026
```
✅ Finale visible (J-1 de la finale)
❌ Phases précédentes invisibles (terminées)
```

## 🔍 Vérification

Pour tester l'affichage :

```php
// Dans Tinker
php artisan tinker

// Voir les matchs actuellement visibles
$matches = \App\Models\MatchGame::where('status', '!=', 'finished')
    ->where('match_date', '>=', now())
    ->get();

// Grouper par phase
$matches->groupBy('phase')->map->count();
```

---

**Dernière mise à jour :** 19 décembre 2025  
**Version :** 2.0 - Filtrage intelligent des phases
