<?php

return [
    'consentModal' => [
        'title' => 'Wij gebruiken cookies',
        'description' => 'Wij gebruiken cookies om de basisfunctionaliteit van de site te waarborgen en ons verkeer te analyseren. Jij beslist welke categorieën je toestaat.',
        'acceptAllBtn' => 'Alles accepteren',
        'acceptNecessaryBtn' => 'Alles weigeren',
        'showPreferencesBtn' => 'Voorkeuren beheren',
    ],
    'preferencesModal' => [
        'title' => 'Cookievoorkeuren',
        'acceptAllBtn' => 'Alles accepteren',
        'acceptNecessaryBtn' => 'Alles weigeren',
        'savePreferencesBtn' => 'Voorkeuren opslaan',
        'closeIconLabel' => 'Sluiten',
        'sections' => [
            [
                'title' => 'Gebruik van cookies',
                'description' => 'Wij gebruiken cookies om basisfunctionaliteit te bieden en je ervaring te verbeteren.',
            ],
            [
                'title' => 'Strikt noodzakelijke cookies',
                'description' => 'Essentieel voor de werking van de website. Altijd actief.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Analytische cookies',
                'description' => 'Helpen ons te begrijpen hoe bezoekers de website gebruiken.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
