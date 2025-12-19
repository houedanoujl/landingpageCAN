# ⚽ Guide des Tirs Au But (TAB)

## ✅ Phases avec TAB Possibles

Les tirs au but sont **disponibles** pour :

1. **1/8e de finale** (`round_of_16`)
2. **1/4 de finale** (`quarter_final`)
3. **1/2 finales** (`semi_final`)
4. **Match pour la 3e place** (`third_place`) ⭐
5. **Finale** (`final`)

❌ **Phase de groupes** (`group_stage`) - Pas de TAB

## 🎯 Côté Utilisateur

### Comment pronostiquer avec TAB

1. **Aller sur la page des pronostics**
2. **Sélectionner un match à élimination directe** (1/8, 1/4, 1/2, 3e place ou finale)
3. **Entrer vos scores**
   - Si vous entrez une **égalité** (ex: 1-1)
   - Une section **"En cas de tirs au but"** apparaît automatiquement
4. **Sélectionner le vainqueur aux TAB**
   - Choisir entre l'équipe domicile ou extérieur
5. **Valider votre pronostic**

### Interface Utilisateur

```blade
<!-- Le formulaire détecte automatiquement la phase -->
@php
    $knockoutPhases = ['round_of_16', 'quarter_final', 'semi_final', 'third_place', 'final'];
    $isKnockoutPhase = in_array($match->phase, $knockoutPhases);
@endphp

<!-- Section TAB visible seulement si : -->
<!-- 1. Phase éliminatoire -->
<!-- 2. Scores égaux -->
@if($isKnockoutPhase)
    <div id="penaltiesSection">
        <!-- Options TAB -->
    </div>
@endif
```

## 🛠️ Côté Admin

### Configuration d'un match avec TAB

1. **Modifier un match** dans `/admin/matches/{id}/edit`
2. **Sélectionner la phase** (1/8, 1/4, 1/2, 3e place ou finale)
3. **Entrer le score final** (égalité, ex: 2-2)
4. **La section TAB apparaît automatiquement**
5. **Cocher** "Ce match a eu des tirs au but"
6. **Sélectionner** le vainqueur (Équipe Domicile ou Extérieur)
7. **Statut** → "Terminé"
8. **Enregistrer**

## 📊 Calcul des Points

### Match Normal (sans TAB)
```
Score : 2-1
Pronostic : 2-1
Points : +1 (participation) +3 (bon vainqueur) +3 (score exact) = 7 pts
```

### Match avec TAB
```
Score : 1-1 → TAB → Équipe A gagne
Pronostic : 1-1 → TAB → Équipe A

Points attribués :
✓ +1 pt  : Participation
✓ +3 pts : Bon vainqueur (TAB)
✗ +0 pts : Score exact (égalité = pas de points score exact)
═══════════════════
  TOTAL : 4 pts
```

### Cas Spéciaux

**Utilisateur prédit TAB mais match normal :**
```
Score réel : 2-1 (pas de TAB)
Pronostic : 1-1 avec TAB → Équipe A

Si l'équipe A gagne réellement → +3 pts bon vainqueur
Sinon → 0 pts
```

**Utilisateur ne prédit pas TAB mais match a TAB :**
```
Score réel : 1-1 → TAB → Équipe B
Pronostic : 2-1 pour Équipe A

Aucun point pour bon vainqueur (mauvaise équipe)
```

## 🔍 Vérifications

### Test Match 3e Place

1. **Créer un match** avec `phase = 'third_place'`
2. **Vérifier côté utilisateur** :
   - Le formulaire affiche bien la section TAB si égalité
3. **Vérifier côté admin** :
   - La section TAB apparaît si score égal
4. **Terminer le match** avec TAB
5. **Vérifier les points** calculés correctement

### Fichiers Modifiés

#### Côté Utilisateur
- ✅ `resources/views/components/prediction-card.blade.php`
  - Ajout section TAB pour phases éliminatoires
  - JavaScript pour affichage dynamique

#### Côté Admin
- ✅ `resources/views/admin/edit-match.blade.php`
  - Section TAB avec détection de phase
  - JavaScript intelligent

#### Logique
- ✅ `app/Jobs/ProcessMatchPoints.php`
  - Gestion des cas TAB
- ✅ `app/Http/Controllers/Web/PredictionController.php`
  - Sauvegarde penalty_winner

## 📱 Interface Mobile

Les sections TAB sont **responsive** :
- Grille 2 colonnes sur mobile
- Boutons tactiles larges
- Texte lisible

## ⚠️ Points Importants

1. **Phase de groupes** → Jamais de TAB
2. **Match 3e place** → TAB possibles ✅
3. **Score exact** → Pas de points si TAB
4. **Vainqueur TAB** → +3 pts si correct

---

**Documentation créée le 19 décembre 2025**
