<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Modération des commentaires
    |--------------------------------------------------------------------------
    |
    | Deux niveaux de liste noire (contexte Sénégal / Maroc / CAN-Mondial) :
    |
    | 'banned'  => rejet automatique : le commentaire est refusé, l'utilisateur
    |              reçoit le 'message' l'invitant à faire preuve de modération.
    | 'review'  => termes ambigus selon contexte : le commentaire est enregistré
    |              masqué (is_moderated = true) en attente de validation admin.
    | 'patterns'=> regex appliquées après normalisation légère (minuscules,
    |              accents supprimés) pour attraper les variantes leetspeak
    |              et espacées (p.u.t.e, f1ls de pu7e...). Niveau : rejet.
    |
    | La vérification des mots est insensible à la casse, aux accents, aux
    | espaces/points intercalés, aux caractères répétés (puuute -> pute) et
    | aux substitutions leetspeak (@=a, 0=o, 1=i, 3=e, 4=a, 5/$=s, 7=t, 9=g).
    | Les termes en écriture arabe sont comparés sur le texte Unicode brut.
    |
    | NOTE : les graphies wolof et darija varient énormément. Liste à faire
    | valider par des locuteurs natifs et à enrichir via les signalements.
    |
    */

    'action' => env('MODERATION_ACTION', 'reject'),

    'message' => "Votre commentaire ne peut pas être publié car il contient des termes inappropriés. Merci de faire preuve de modération et de respect envers les autres participants. ⚽🤝",

    'message_review' => 'Votre commentaire sera publié après validation par un modérateur.',

    // Nombre de signalements distincts à partir duquel un commentaire est
    // automatiquement masqué en attente de modération humaine.
    'report_threshold' => (int) env('MODERATION_REPORT_THRESHOLD', 3),

    'banned' => [
        // Insultes courantes (FR)
        'connard', 'conard', 'connasse', 'conasse', 'salope', 'salopes', 'grosse salope', 'salaud',
        'enculé', 'encule', 'enkulé', 'enculer', 'enculer ta',
        'pute', 'putes', 'putain', 'grosse pute', 'sale pute', 'pute de mère',
        'pd', 'pédé', 'pede', 'sale pd', 'tapette', 'tarlouze', 'tafiole', 'fiotte',
        'batard', 'bâtard', 'batar', 'bâtards',
        'ducon', 'abruti', 'crétin', 'débile',
        'ferme ta gueule', 'ta gueule', 'tg', 'ftg',
        'nique', 'niquer', 'ntm', 'fdp', 'fils de pute', 'fils de p', 'filsdepute',
        'nique ta mère', 'nique ta mere', 'niktamere', 'nik ta mere',
        'ta mère la pute', 'ta mere la pute', 'tmlp',
        'enfoiré', 'enfoire',
        'suce ma', 'suceur', 'suceuse',
        'attardé', 'attarde', 'trisomique', 'mongolien',
        'ta race', 'nique ta race', 'ntr',
        'va te faire enculer', 'vtfe', 'va te faire foutre', 'vtff',
        'merdeux', 'petite merde', 'tas de merde', 'sac à merde',
        'abrutis de', 'bande de cons', 'gros con', 'grosse conne',

        // Insultes / haine (EN)
        'fuck', 'fucker', 'motherfucker', 'bitch', 'asshole', 'bastard',
        'cunt', 'dickhead', 'faggot',

        // Slurs racistes (FR)
        'nègre', 'negre', 'négro', 'negro', 'negros', 'nigger', 'nigga',
        'bamboula', 'bamboulas', 'macaque', 'macaques',
        'bougnoule', 'bougnoules', 'bougnoul', 'bicot', 'bicots',
        'raton', 'ratons', 'crouille', 'crouilles', 'youpin', 'chinetoque',
        'sale noir', 'sale noire', 'sales noirs',
        'sale arabe', 'sales arabes',
        'sale africain', 'sales africains',
        'sale marocain', 'sales marocains',
        'sale sénégalais', 'sale senegalais', 'sales sénégalais',
        'sale mauritanien', 'sales mauritaniens',
        'sale musulman', 'sales musulmans', 'sale muslim',
        'sale juif', 'sales juifs', 'sale blanc', 'sales blancs',
        'banania', 'y a bon banania', 'bounty',

        // Wolof
        'doomu xaraam', 'domou haram', 'doomou haram', 'dom haram', 'doom haram', 'domu haram',
        'saga sa ndey', 'saga sa yaay',
        'sa ndey', 'sa yaay bu', 'say yaye',
        'yàlla na la dee', 'yalla nala dee',
        'saytaane bi', 'saytane bi',
        'kataa', 'kataay',
        'xale bu doff bi', 'dof bi', 'doff bi', 'ndoff bi',

        // Darija / arabe translittéré
        'zamel', 'zaml', 'zamell', 'zwamel',
        'qahba', 'kahba', '9ahba', '9a7ba', 'kahbas',
        'weld l9ahba', 'weld lkahba', 'wld l9hba', 'ould kahba', 'ould el kahba',
        'nik mok', 'nikmok', 'nik omok', 'naal din', 'n3al din', 'naaldin',
        'sir t9awed', 'tqawed', 't9awd', 'tqawd', 'mqawed', 'm9awed',
        'ya kelb', 'ya klb', 'klab', 'wlad lklab', 'ould kelb', 'ibn kelb', 'ibn el kelb',
        'ya hmar', 'ya 7mar', 'hmir', '7mir',
        'ya khara', 'ya kha ra', 'khra alik', 'kol khra',
        'zebbi', 'zbi', 'zebi',
        'sharmouta', 'charmouta', 'ibn sharmouta', 'ben charmouta',
        'kos omak', 'koss omok', 'kus umak', 'kosomak',
        'azzi', '3azzi', 'el azzi', 'laazzi',
        'kahlouch', 'ka7louch', 'kehlouch', 'kahloucha', 'khlouch',
        'l3abid', 'ya abid', 'ya 3abd', 'wlad l3abid', 'abid afrique',

        // Arabe (écriture arabe)
        'كلب', 'يا كلب', 'ابن الكلب', 'كلاب',
        'حمار', 'يا حمار',
        'قحبة', 'ولد القحبة', 'بن القحبة',
        'زامل', 'زوامل',
        'شرموطة', 'ابن الشرموطة',
        'عبد', 'عبيد', 'يا عبد', 'العبيد',
        'كحلوش', 'الكحلوش',
        'عزي', 'العزي',
        'خرا', 'يا خرا',
        'كس امك', 'كس أمك',
        'نيك امك', 'نيك أمك',
        'نعل دين', 'ينعل دين',

        // Phrases de rivalité / haine communautaire
        "retournez dans votre désert", "retournez dans votre desert",
        "retourne dans ton désert", "retourne dans ton desert",
        "vous n'êtes pas des africains", "vous netes pas africains", "pas des vrais africains",
        "esclaves des arabes", "esclave des arabes",
        "vendeurs d'esclaves", "vendeur d'esclaves", "marchands d'esclaves",
        "race d'esclaves", "descendants d'esclaves",
        "rentrez chez vous les arabes", "rentrez chez vous les noirs",
        "l'afrique aux africains noirs", "dégagez de l'afrique", "degagez de l'afrique",
        "mangeurs de couscous de merde",
        "sales chameliers", "fils de chamelier",
        "terroristes de merde", "pays de terroristes",

        // Contenu sexuel explicite
        'porn', 'porno', 'pornographie', 'bite', 'penis', 'pénis',
        'vagin', 'chatte', 'sexe', 'baiser', 'sodomie', 'fellation',
        'masturbation', 'sperme',
    ],

    /*
    | Termes à double usage : ne pas bloquer automatiquement (faux positifs
    | probables : "chameau" dans un contexte neutre, "esclave" historique...).
    | Le commentaire est mis en attente de modération humaine.
    */
    'review' => [
        'naar', 'naar bi', 'naar yi',
        'guélèm', 'guelem', 'gelem',
        'chamelier', 'chameliers', 'chameau',
        'esclave', 'esclaves', 'esclavagiste', 'esclavagistes',
        'singe', 'singes', 'gorille', 'babouin',
        'couscous', 'couscoussier',
        'kafir', 'kouffar', 'kuffar', 'kafer',
        'mécréant', 'mecreant', 'mécréants',
        'terroriste', 'terroristes', 'daech', 'daesh',
        'colonisé', 'colonisés', 'colon', 'colons',
        'berbère de', 'harki', 'harkis',
        'doff', 'dof', 'saytaane', 'saytane',
        'toubab', 'raciste', 'racistes',
        'haram', 'haramzade',
        'tricheurs', 'voleurs de match', 'arbitre acheté', 'arbitres achetés',
        'moricaud', 'noiraud',
        'sauvage', 'sauvages', 'primitif', 'primitifs',
        'sous-développé', 'sous developpes',
    ],

    /*
    | Regex pour les variantes avec espaces/ponctuation/leetspeak.
    | Appliquées sur le texte en minuscules, accents supprimés (espaces conservés).
    | Niveau : rejet automatique.
    */
    // Les lookarounds (?<![a-z0-9]) / (?![a-z0-9]) évitent les faux positifs
    // par sous-chaîne : "disputé", "députés", "pote", "basculé"...
    'patterns' => [
        '/f[i1!|]+l+s+\s*d[e3]\s*p+[uü*]+t+[e3]*/u',
        '/n+[i1!]+q*u*[e3]*\s*t+a+\s*m+[eè3]+r+[e3]*/u',
        '/(?<![a-z0-9])[e3]n+c+u+l+[eé3]/u',
        // 'o' exclu de la classe pour ne pas bloquer "pote"
        '/(?<![a-z0-9])p+[uü*0]+t+[e3]+s*(?![a-z0-9])/u',
        '/(?<![a-z0-9])s+a+l+[o0]+p+[e3]+s*(?![a-z0-9])/u',
        '/(?<![a-z0-9])b[aâ4@]+t+a+r+d*(?![a-z0-9])/u',
        '/b[o0]ugn[o0]ul/u',
        '/(?<![a-z0-9])n[eéè3]+g+r+[o0e3]+s*(?![a-z0-9])/u',
        '/b[a4@]mb[o0]ul[a4@]/u',
        '/(?<![a-z0-9])z[a4@]m[e3]l+(?![a-z0-9])/u',
        '/(?<![a-z0-9])[9q]a*h*7*ba/u',
        '/k[a4]h*7*l[o0]u*ch/u',
        '/(?<![a-z0-9])3?a+z+z+i+\b/u',
        '/(?<![a-z0-9])d[o0]+m+[ou]*\s*[xh]*a+r+a+m/u',
    ],
];
