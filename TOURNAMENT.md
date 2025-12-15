# 🏆 Système de Gestion de Tournoi CAN 2025

## Vue d'ensemble

Le système de gestion de tournoi permet de gérer automatiquement toutes les phases de la Coupe d'Afrique des Nations :
- **Phase de poules** (6 groupes de 4 équipes)
- **1/8e de finale** (16 équipes)
- **1/4 de finale** (8 équipes)
- **1/2 finale** (demi-finales - 4 équipes)
- **Match pour la 3e place**
- **Finale**

## Architecture du système

### Phases du tournoi

| Phase | Code | Nombre de matchs | Qualification |
|-------|------|------------------|---------------|
| Phase de poules | `group_stage` | 36 matchs | 16 équipes (1er et 2e de chaque groupe + 4 meilleurs 3e) |
| 1/8e de finale | `round_of_16` | 8 matchs | 8 équipes gagnantes |
| 1/4 de finale | `quarter_final` | 4 matchs | 4 équipes gagnantes |
| 1/2 finale | `semi_final` | 2 matchs | 2 gagnants → Finale, 2 perdants → 3e place |
| 3e place | `third_place` | 1 match | Médaille de bronze |
| Finale | `final` | 1 match | Champion |

## Fonctionnalités

### 1. Qualification automatique

Lorsqu'un match à élimination directe se termine :
- ✅ Le gagnant est **automatiquement qualifié** pour le prochain tour
- ✅ L'équipe gagnante est **automatiquement assignée** au match suivant
- ✅ Les matchs enfants sont **mis à jour en temps réel**

**Exemple** :
```
Match 1 des 1/8e : France 2-1 Sénégal
→ France est automatiquement qualifiée pour le quart de finale
→ Le match de quart correspondant affiche "France" au lieu de "TBD"
```

### 2. Calcul du classement des poules

Le service `TournamentService` calcule automatiquement le classement de chaque groupe selon les critères officiels :
1. **Points** (3 pour victoire, 1 pour nul, 0 pour défaite)
2. **Différence de buts** (en cas d'égalité de points)
3. **Buts marqués** (en cas d'égalité de différence)

### 3. Sélection des meilleurs 3èmes

Pour la CAN, les **4 meilleurs 3èmes** se qualifient également pour les 1/8e de finale.

Le système :
- Compare tous les 3èmes de chaque groupe
- Sélectionne les 4 meilleurs selon points → différence → buts marqués
- Les assigne automatiquement aux bons matchs de 1/8e

## Utilisation

### Créer le tableau à élimination directe

```php
use App\Services\TournamentService;

$tournamentService = new TournamentService();

// Créer automatiquement tous les matchs à élimination directe
// (1/8e, 1/4, 1/2, finale, 3e place) avec les liens parent-enfant
$bracket = $tournamentService->createKnockoutBracket();
```

Cela crée :
- 1 finale
- 1 match pour la 3e place
- 2 demi-finales
- 4 quarts de finale
- 8 matchs de 1/8e de finale

Tous les matchs sont liés automatiquement via `parent_match_1_id` et `parent_match_2_id`.

### Qualifier les équipes depuis la phase de poules

```php
// 1. S'assurer que tous les matchs de poules sont terminés
// 2. Lancer la qualification
$result = $tournamentService->qualifyTeamsFromGroupStage();

// Résultat :
// [
//     'qualified_teams' => [...], // 1ers et 2es de chaque groupe
//     'best_thirds' => [...]      // 4 meilleurs 3èmes
// ]
```

### Mettre à jour un match terminé

Lorsqu'un match se termine, la qualification est **automatique** grâce à `MatchObserver` :

```php
// Dans l'admin, quand vous marquez un match comme terminé :
$match->update([
    'status' => 'finished',
    'score_a' => 2,
    'score_b' => 1,
]);

// → L'observateur détecte le changement
// → Le service TournamentService qualifie automatiquement l'équipe gagnante
// → Le match enfant est mis à jour avec l'équipe qualifiée
```

## Structure de la base de données

### Nouvelles colonnes dans `matches`

| Colonne | Type | Description |
|---------|------|-------------|
| `phase` | enum | Phase du tournoi (group_stage, round_of_16, etc.) |
| `match_number` | int | Numéro du match dans la phase (1, 2, 3...) |
| `bracket_position` | int | Position dans le tableau (pour l'affichage graphique) |
| `display_order` | int | Ordre d'affichage dans la liste |
| `parent_match_1_id` | foreignId | Match parent 1 (le gagnant vient de ce match) |
| `parent_match_2_id` | foreignId | Match parent 2 (le gagnant vient de ce match) |
| `winner_goes_to` | enum | Position du gagnant dans le match enfant (home/away) |

### Relations Eloquent

```php
// Match parent (d'où viennent les équipes)
$match->parentMatch1();  // Premier match parent
$match->parentMatch2();  // Deuxième match parent

// Matchs enfants (où va le gagnant)
$match->childMatches();  // Tous les matchs qui dépendent de celui-ci

// Gagnant du match
$winnerId = $match->winner_team_id;  // ID de l'équipe gagnante
```

## Interface Admin

### Créer les matchs de poules

1. Accédez à `/admin/matches/create`
2. Sélectionnez :
   - Phase : "Phase de poules"
   - Équipe à domicile
   - Équipe extérieure
   - Groupe (A, B, C, D, E, F)
   - Date et heure
3. Le système crée le match

### Générer le tableau à élimination directe

**Option 1 : Via Tinker (recommandé)**
```bash
php artisan tinker

$service = new App\Services\TournamentService();
$bracket = $service->createKnockoutBracket();
exit
```

**Option 2 : Via une commande artisan (à créer)**
```bash
php artisan tournament:generate-bracket
```

### Terminer un match et qualifier automatiquement

1. Accédez à `/admin/matches/{id}/edit`
2. Entrez les scores
3. Changez le statut à "Terminé"
4. Cliquez sur "Mettre à jour"

→ **Le gagnant est automatiquement qualifié pour le prochain tour** 🎉

## Affichage Public

### Afficher les matchs par phase

```blade
{{-- Dans votre vue Blade --}}
@php
    $phases = [
        'group_stage' => 'Phase de poules',
        'round_of_16' => '1/8e de finale',
        'quarter_final' => 'Quart de finale',
        'semi_final' => 'Demi-finale',
        'third_place' => '3e place',
        'final' => 'Finale',
    ];
@endphp

@foreach($phases as $phaseCode => $phaseName)
    <h2>{{ $phaseName }}</h2>

    @php
        $matches = \App\Models\MatchGame::where('phase', $phaseCode)
            ->orderBy('display_order')
            ->get();
    @endphp

    @foreach($matches as $match)
        <div class="match-card">
            <span>{{ $match->team_a ?? 'TBD' }}</span>
            vs
            <span>{{ $match->team_b ?? 'TBD' }}</span>

            @if($match->status === 'finished')
                <span>{{ $match->score_a }} - {{ $match->score_b }}</span>
            @endif
        </div>
    @endforeach
@endforeach
```

### Afficher le bracket visuel

Pour afficher un bracket graphique comme sur les sites sportifs :

```php
// Récupérer tous les matchs à élimination directe
$knockoutMatches = MatchGame::whereIn('phase', [
    'round_of_16', 'quarter_final', 'semi_final', 'third_place', 'final'
])
->orderBy('phase')
->orderBy('bracket_position')
->get()
->groupBy('phase');
```

Vous pouvez ensuite utiliser une bibliothèque comme **Bracket.js** ou **react-tournament-bracket** pour l'affichage visuel.

## Exemple de flux complet

### 1. Phase de poules

```
Groupe A : Maroc, RDC, Zambie, Tanzanie
Groupe B : Égypte, Ghana, Mozambique, Cap-Vert
...

36 matchs au total (6 groupes × 6 matchs par groupe)
```

### 2. Fin de la phase de poules

```php
// Calculer les qualifiés
$service = new TournamentService();
$qualified = $service->qualifyTeamsFromGroupStage();

// Résultat :
// Groupe A : 1er Maroc, 2e RDC, 3e Zambie
// Groupe B : 1er Égypte, 2e Ghana, 3e Cap-Vert
// ...
// Meilleurs 3èmes : Zambie, Cap-Vert, Angola, Guinée
```

### 3. 1/8e de finale

```
Match 1 : Maroc (1A) vs Zambie (3ème C/D/E/F)
Match 2 : RDC (2A) vs Ghana (2B)
...
```

Quand ces matchs se terminent → **Qualification automatique** pour les quarts !

### 4. Finale

```
Match : Sénégal vs Cameroun
Score : 1-0
→ Sénégal est couronné champion de la CAN 2025 ! 🏆
```

## Commandes utiles

```bash
# Voir le classement d'un groupe
php artisan tinker
$service = new App\Services\TournamentService();
$standings = $service->calculateGroupStandings('A');
print_r($standings);

# Créer le bracket complet
$bracket = $service->createKnockoutBracket();

# Qualifier les équipes manuellement
$result = $service->qualifyTeamsFromGroupStage();
```

## Points d'attention

### ⚠️ Égalités en phase à élimination directe

Actuellement, le système ne gère pas les tirs au but (penalties).

Pour l'implémenter :
1. Ajoutez une colonne `penalty_winner_id` dans la table `matches`
2. Modifiez `getWinnerTeamIdAttribute()` pour vérifier les penalties en cas d'égalité
3. Ajoutez un champ dans le formulaire admin pour saisir le gagnant aux tirs au but

### ⚠️ Match pour la 3e place

Les perdants des demi-finales doivent être assignés manuellement au match pour la 3e place ou via une logique supplémentaire.

### ✅ Avantages du système

- 🚀 **Automatisation complète** : Plus besoin de saisir manuellement les équipes qualifiées
- 🎯 **Zéro erreur** : Pas de risque d'oubli ou d'erreur de saisie
- ⏱️ **Temps réel** : Les matchs suivants sont mis à jour instantanément
- 📊 **Traçabilité** : Chaque match connaît ses parents et ses enfants
- 🏗️ **Scalable** : Fonctionne pour n'importe quel format de tournoi

## Prochaines étapes

1. ✅ Migration effectuée
2. ✅ Service créé
3. ✅ Observer configuré
4. ⏳ Créer l'interface admin pour générer le bracket (bouton "Générer le tableau")
5. ⏳ Créer une vue publique pour afficher le bracket visuel
6. ⏳ Ajouter la gestion des penalties
7. ⏳ Ajouter les notifications push quand une équipe se qualifie

Félicitations ! Votre système de tournoi est maintenant opérationnel ! 🎉
