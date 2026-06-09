<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modération des commentaires
    |--------------------------------------------------------------------------
    |
    | 'action' :
    |   'reject'  => le commentaire est refusé, l'utilisateur reçoit une erreur.
    |   'hold'    => le commentaire est enregistré masqué (is_moderated = true)
    |                en attente de validation par un admin.
    |
    | 'banned' : liste noire de mots/expressions interdits.
    | La vérification est insensible à la casse, aux accents, aux espaces et
    | aux substitutions courantes (leetspeak : @=a, 0=o, 1=i, 3=e, 5/$=s ...).
    | Ajoutez/éditez librement les termes ci-dessous.
    |
    */

    'action' => env('MODERATION_ACTION', 'reject'),

    'message' => 'Votre commentaire contient des termes interdits et ne peut pas être publié.',

    'banned' => [
        // Insultes courantes (FR)
        'connard', 'connasse', 'salope', 'salaud', 'enculé', 'enculer',
        'pute', 'putain', 'pd', 'pédé', 'tapette', 'batard', 'bâtard',
        'ducon', 'abruti', 'crétin', 'débile', 'ferme ta gueule', 'ta gueule',
        'nique', 'niquer', 'ntm', 'fdp', 'fils de pute',

        // Insultes / haine (EN)
        'fuck', 'fucker', 'motherfucker', 'bitch', 'asshole', 'bastard',
        'cunt', 'dickhead', 'faggot',

        // Racisme
        'negre', 'nègre', 'negro', 'nigger', 'nigga', 'bougnoule',
        'youpin', 'bamboula', 'chinetoque', 'sale arabe', 'sale noir',
        'sale juif', 'sale blanc',

        // Contenu sexuel explicite
        'porn', 'porno', 'pornographie', 'bite', 'penis', 'pénis',
        'vagin', 'chatte', 'sexe', 'baiser', 'sodomie', 'fellation',
        'masturbation', 'sperme',
    ],
];
