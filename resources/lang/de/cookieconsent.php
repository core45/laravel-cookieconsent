<?php

return [
    'consentModal' => [
        'title' => 'Wir verwenden Cookies',
        'description' => 'Wir verwenden Cookies, um die grundlegenden Funktionen der Website sicherzustellen und unseren Traffic zu analysieren. Sie entscheiden, welche Kategorien Sie zulassen.',
        'acceptAllBtn' => 'Alle akzeptieren',
        'acceptNecessaryBtn' => 'Alle ablehnen',
        'showPreferencesBtn' => 'Einstellungen verwalten',
    ],
    'preferencesModal' => [
        'title' => 'Cookie-Einstellungen',
        'acceptAllBtn' => 'Alle akzeptieren',
        'acceptNecessaryBtn' => 'Alle ablehnen',
        'savePreferencesBtn' => 'Einstellungen speichern',
        'closeIconLabel' => 'Schließen',
        'sections' => [
            [
                'title' => 'Cookie-Nutzung',
                'description' => 'Wir verwenden Cookies, um grundlegende Funktionen bereitzustellen und Ihr Erlebnis zu verbessern.',
            ],
            [
                'title' => 'Streng notwendige Cookies',
                'description' => 'Essentiell für die Funktion der Website. Immer aktiv.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Analyse-Cookies',
                'description' => 'Helfen uns zu verstehen, wie Besucher die Website nutzen.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
