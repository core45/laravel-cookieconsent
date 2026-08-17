<?php

return [
    'consentModal' => [
        'title' => 'Koristimo kolačiće',
        'description' => 'Koristimo kolačiće kako bismo osigurali osnovne funkcije stranice i analizirali promet. Vi odlučujete koje kategorije dopuštate.',
        'acceptAllBtn' => 'Prihvati sve',
        'acceptNecessaryBtn' => 'Odbij sve',
        'showPreferencesBtn' => 'Upravljaj postavkama',
    ],
    'preferencesModal' => [
        'title' => 'Postavke kolačića',
        'acceptAllBtn' => 'Prihvati sve',
        'acceptNecessaryBtn' => 'Odbij sve',
        'savePreferencesBtn' => 'Spremi postavke',
        'closeIconLabel' => 'Zatvori',
        'sections' => [
            [
                'title' => 'Korištenje kolačića',
                'description' => 'Koristimo kolačiće kako bismo osigurali osnovne funkcije i poboljšali vaše iskustvo.',
            ],
            [
                'title' => 'Strogo nužni kolačići',
                'description' => 'Nužni za funkcioniranje web stranice. Uvijek aktivni.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Analitički kolačići',
                'description' => 'Pomažu nam da razumijemo kako posjetitelji koriste web stranicu.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
