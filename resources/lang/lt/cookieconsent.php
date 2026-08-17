<?php

return [
    'consentModal' => [
        'title' => 'Naudojame slapukus',
        'description' => 'Naudojame slapukus, kad užtikrintumėme pagrindinę svetainės funkciją ir analizuotume srautą. Jūs nusprendžiate, kurias kategorijas leidžiate.',
        'acceptAllBtn' => 'Priimti visus',
        'acceptNecessaryBtn' => 'Atmesti visus',
        'showPreferencesBtn' => 'Tvarkyti nustatymus',
    ],
    'preferencesModal' => [
        'title' => 'Slapukų nustatymai',
        'acceptAllBtn' => 'Priimti visus',
        'acceptNecessaryBtn' => 'Atmesti visus',
        'savePreferencesBtn' => 'Išsaugoti nustatymus',
        'closeIconLabel' => 'Uždaryti',
        'sections' => [
            [
                'title' => 'Slapukų naudojimas',
                'description' => 'Naudojame slapukus, kad pateiktume pagrindines funkcijas ir pagerintume jūsų patirtį.',
            ],
            [
                'title' => 'Būtini slapukai',
                'description' => 'Būtini svetainei veikti. Visada aktyvūs.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Analitiniai slapukai',
                'description' => 'Padeda mums suprasti, kaip lankytojai naudoja svetainę.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
