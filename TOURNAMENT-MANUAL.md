# 🎮 Guide de Gestion Manuelle du Tournoi CAN 2025

## Vue d'ensemble

Le système de tournoi est maintenant configuré pour une **gestion 100% manuelle** depuis l'interface admin. Vous contrôlez chaque étape du tournoi.

## ✅ Modifications effectuées

### 1. Qualification automatique désactivée
- L'observateur `MatchObserver` a été désactivé
- Les équipes ne se qualifient plus automatiquement quand un match se termine
- **Vous contrôlez** toutes les qualifications manuellement

### 2. Nouvelles pages admin créées

| Page | URL | Description |
|------|-----|-------------|
| **Gestion du tournoi** | `/admin/tournament` | Page principale de gestion |
| **Matchs par phase** | `/admin/tournament/phase/{phase}` | Gérer les matchs d'une phase spécifique |

### 3. Nouvelles fonctionnalités

✅ Afficher les classements des groupes en temps réel
✅ Générer le bracket complet (1/8e → Finale) en un clic
✅ Calculer les équipes qualifiées depuis les poules
✅ Qualifier manuellement n'importe quelle équipe pour n'importe quel match
✅ Voir d'où proviennent les équipes (matchs parents)

## 📖 Guide d'utilisation

### Étape 1 : Phase de poules (36 matchs)

1. Créez tous les matchs de poules normalement via `/admin/matches/create`
2. Sélectionnez la phase "Phase de poules"
3. Assignez chaque match à son groupe (A, B, C, D, E, F)
4. Terminez les matchs au fur et à mesure

**Voir les classements :**
- Allez sur `/admin/tournament`
- Les classements se mettent à jour automatiquement
- Les 2 premiers de chaque groupe sont marqués "✓ Qualifié"
- Les 3èmes sont marqués "? Peut-être" (4 meilleurs 3èmes se qualifient)

### Étape 2 : Générer le bracket (1 fois)

**Quand ?** Après que tous les matchs de poules soient terminés

**Comment ?**
1. Allez sur `/admin/tournament`
2. Cliquez sur **"🚀 Générer le bracket complet"**
3. Cela crée automatiquement :
   - 8 matchs de 1/8e de finale
   - 4 matchs de 1/4 de finale
   - 2 matchs de 1/2 finale (demi-finales)
   - 1 match pour la 3e place
   - 1 finale

**Résultat :**
- Tous les matchs sont créés avec status "TBD" (To Be Determined)
- Les liens parent-enfant sont configurés automatiquement
- Les équipes ne sont PAS encore assignées

### Étape 3 : Qualifier les équipes pour les 1/8e (manuel)

**Option A : Calcul automatique (recommandé)**

1. Sur `/admin/tournament`, cliquez sur **"📊 Calculer les qualifiés"**
2. Le système calcule automatiquement les 16 équipes qualifiées :
   - 1er et 2e de chaque groupe (12 équipes)
   - 4 meilleurs 3èmes (4 équipes)
3. ⚠️ **Vous devez quand même assigner manuellement les équipes aux matchs**

**Option B : Qualification 100% manuelle**

1. Allez sur `/admin/tournament/phase/round_of_16`
2. Pour chaque match :
   - Cliquez sur **"✏️ Qualifier équipe"**
   - Sélectionnez l'équipe dans la liste
   - Cliquez sur "Valider"
3. Faites cela pour l'équipe à domicile ET l'équipe extérieure de chaque match

**Exemple concret :**
```
Match 1 : 1er Groupe A vs 3ème meilleur (C/D/E/F)
→ Cliquez sur "Qualifier équipe" à gauche
→ Sélectionnez "Maroc" (1er du groupe A)
→ Cliquez sur "Qualifier équipe" à droite
→ Sélectionnez "Zambie" (3ème meilleur)
```

### Étape 4 : Terminer les matchs de 1/8e

1. Allez sur `/admin/matches/{id}/edit` pour chaque match de 1/8e
2. Entrez les scores
3. Changez le statut à "Terminé"
4. Cliquez sur "Mettre à jour"

⚠️ **Important :** Les équipes ne se qualifient PAS automatiquement !

### Étape 5 : Qualifier les équipes pour les 1/4

1. Allez sur `/admin/tournament/phase/quarter_final`
2. Regardez la section **"📌 Provenance des équipes"** de chaque match
3. Pour chaque match de 1/4 :
   - Identifiez le gagnant du match parent 1
   - Qualifiez-le pour l'équipe à domicile
   - Identifiez le gagnant du match parent 2
   - Qualifiez-le pour l'équipe extérieure

**Exemple :**
```
Quart de finale 1 :
• Équipe à domicile : Gagnant du Match 1 des 1/8e
• Équipe extérieure : Gagnant du Match 2 des 1/8e

Si Match 1 : Maroc 2-1 Zambie → Qualifier "Maroc" à domicile
Si Match 2 : Sénégal 3-0 Ghana → Qualifier "Sénégal" à l'extérieur
```

### Étape 6 : Répéter pour toutes les phases

Pour chaque phase (1/4, 1/2, finale) :

1. **Terminez tous les matchs** de la phase précédente
2. **Allez sur la page** de la phase suivante
3. **Qualifiez manuellement** toutes les équipes
4. **Terminez les matchs**
5. Passez à la phase suivante

## 🎯 Raccourcis clavier (navigation)

| Raccourci | Action |
|-----------|--------|
| `/admin/tournament` | Page principale |
| `/admin/tournament/phase/group_stage` | Phase de poules |
| `/admin/tournament/phase/round_of_16` | 1/8e de finale |
| `/admin/tournament/phase/quarter_final` | 1/4 de finale |
| `/admin/tournament/phase/semi_final` | Demi-finales |
| `/admin/tournament/phase/third_place` | 3e place |
| `/admin/tournament/phase/final` | Finale |

## 💡 Conseils pratiques

### Vérifier les classements en temps réel
```
1. Allez sur /admin/tournament
2. Les classements se mettent à jour automatiquement
3. Vérifiez les points, différence de buts, buts marqués
```

### Identifier rapidement les qualifiés
```
Sur la page /admin/tournament :
• Fond vert = Qualifié (1er ou 2e)
• Fond jaune = Peut-être qualifié (3ème)
• Fond blanc = Non qualifié
```

### Éviter les erreurs
```
✅ Vérifiez toujours d'où proviennent les équipes (matchs parents)
✅ Assurez-vous que le match parent est terminé avant de qualifier
✅ Vérifiez le gagnant avant de qualifier
❌ Ne qualifiez pas une équipe qui a perdu !
```

## 🔧 Dépannage

### "Aucun match dans cette phase"
**Solution :** Vous devez d'abord générer le bracket depuis `/admin/tournament`

### "Les classements sont vides"
**Cause :** Aucun match de poule n'est terminé
**Solution :** Terminez au moins quelques matchs de chaque groupe

### "Je ne vois pas l'équipe dans la liste"
**Cause :** L'équipe n'existe pas dans la table `teams`
**Solution :** Créez l'équipe via `/admin/teams/create`

### "Comment annuler une qualification ?"
**Solution :**
1. Allez sur le match concerné : `/admin/matches/{id}/edit`
2. Changez l'équipe dans les champs "Équipe à domicile" ou "Équipe extérieure"
3. OU supprimez l'équipe et remettez "TBD" manuellement

## 📊 Exemple de flux complet

```
1. Créer les 36 matchs de poules
   ↓
2. Terminer tous les matchs de poules
   ↓
3. Aller sur /admin/tournament
   ↓
4. Cliquer sur "Générer le bracket complet"
   ↓
5. Cliquer sur "Calculer les qualifiés" (optionnel)
   ↓
6. Aller sur /admin/tournament/phase/round_of_16
   ↓
7. Qualifier manuellement les 16 équipes dans les 8 matchs
   ↓
8. Terminer les 8 matchs de 1/8e
   ↓
9. Aller sur /admin/tournament/phase/quarter_final
   ↓
10. Qualifier manuellement les 8 équipes dans les 4 matchs
    ↓
11. Terminer les 4 matchs de 1/4
    ↓
12. Aller sur /admin/tournament/phase/semi_final
    ↓
13. Qualifier manuellement les 4 équipes dans les 2 matchs
    ↓
14. Terminer les 2 matchs de 1/2
    ↓
15. Qualifier pour la finale ET pour la 3e place
    ↓
16. Terminer la finale → Couronner le champion ! 🏆
```

## ⚡ Pour réactiver la qualification automatique

Si vous changez d'avis et voulez la qualification automatique :

1. Ouvrez `app/Observers/MatchObserver.php`
2. Ligne 34-54 : Décommentez le code
3. Les équipes se qualifieront automatiquement quand un match se termine

## 🎊 Félicitations !

Vous avez maintenant le contrôle total sur votre tournoi CAN 2025 ! 🏆

Pour toute question, consultez `TOURNAMENT.md` pour plus de détails techniques.
