<?php

return [
    'consentModal' => [
        'title' => 'Nous utilisons des cookies',
        'description' => 'Nous utilisons des cookies pour garantir le fonctionnement de base du site et analyser notre trafic. Vous décidez quelles catégories autoriser.',
        'acceptAllBtn' => 'Tout accepter',
        'acceptNecessaryBtn' => 'Tout refuser',
        'showPreferencesBtn' => 'Gérer les préférences',
    ],
    'preferencesModal' => [
        'title' => 'Préférences des cookies',
        'acceptAllBtn' => 'Tout accepter',
        'acceptNecessaryBtn' => 'Tout refuser',
        'savePreferencesBtn' => 'Enregistrer les préférences',
        'closeIconLabel' => 'Fermer',
        'sections' => [
            [
                'title' => 'Utilisation des cookies',
                'description' => 'Nous utilisons des cookies pour fournir les fonctions de base et améliorer votre expérience.',
            ],
            [
                'title' => 'Cookies strictement nécessaires',
                'description' => 'Indispensables au fonctionnement du site. Toujours actifs.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Cookies analytiques',
                'description' => 'Nous aident à comprendre comment les visiteurs utilisent le site.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
