<?php

return [
    'consentModal' => [
        'title' => 'Používame súbory cookie',
        'description' => 'Používame súbory cookie na zabezpečenie základných funkcií webu a na analýzu našej návštevnosti. Vy rozhodujete, ktoré kategórie povolíte.',
        'acceptAllBtn' => 'Prijať všetko',
        'acceptNecessaryBtn' => 'Odmietnuť všetko',
        'showPreferencesBtn' => 'Spravovať nastavenia',
    ],
    'preferencesModal' => [
        'title' => 'Nastavenie súborov cookie',
        'acceptAllBtn' => 'Prijať všetko',
        'acceptNecessaryBtn' => 'Odmietnuť všetko',
        'savePreferencesBtn' => 'Uložiť nastavenia',
        'closeIconLabel' => 'Zavrieť',
        'sections' => [
            [
                'title' => 'Používanie súborov cookie',
                'description' => 'Používame súbory cookie na poskytovanie základných funkcií a zlepšenie vášho zážitku.',
            ],
            [
                'title' => 'Zásadne nevyhnutné súbory cookie',
                'description' => 'Nevyhnutné pre fungovanie webu. Vždy aktívne.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Analytické súbory cookie',
                'description' => 'Pomáhajú nám pochopiť, ako návštevníci používajú web.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
