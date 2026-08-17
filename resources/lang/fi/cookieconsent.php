<?php

return [
    'consentModal' => [
        'title' => 'Käytämme evästeitä',
        'description' => 'Käytämme evästeitä varmistamaan sivuston perustoiminnallisuuden ja analysoimaan liikennettä. Sinä päätät, mitkä kategoriat sallit.',
        'acceptAllBtn' => 'Hyväksy kaikki',
        'acceptNecessaryBtn' => 'Hylkää kaikki',
        'showPreferencesBtn' => 'Hallitse asetuksia',
    ],
    'preferencesModal' => [
        'title' => 'Evästeasetukset',
        'acceptAllBtn' => 'Hyväksy kaikki',
        'acceptNecessaryBtn' => 'Hylkää kaikki',
        'savePreferencesBtn' => 'Tallenna asetukset',
        'closeIconLabel' => 'Sulje',
        'sections' => [
            [
                'title' => 'Evästeiden käyttö',
                'description' => 'Käytämme evästeitä tarjoamaan perustoiminnallisuuden ja parantamaan kokemustasi.',
            ],
            [
                'title' => 'Ehdottoman välttämättömät evästeet',
                'description' => 'Välttämättömiä sivuston toiminnalle. Aina aktiiviset.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Analytiikkaevästeet',
                'description' => 'Auttavat ymmärtämään, miten vieraat käyttävät sivustoa.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
