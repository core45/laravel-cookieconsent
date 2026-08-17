<?php

return [
    'consentModal' => [
        'title' => 'Wykorzystujemy pliki cookies',
        'description' => 'Wykorzystujemy pliki cookies, aby zapewnić podstawowe funkcje strony i analizować ruch. Ty decydujesz, które kategorie akceptujesz.',
        'acceptAllBtn' => 'Zaakceptuj wszystkie',
        'acceptNecessaryBtn' => 'Odrzuć wszystkie',
        'showPreferencesBtn' => 'Zarządzaj preferencjami',
    ],
    'preferencesModal' => [
        'title' => 'Preferencje dotyczące plików cookies',
        'acceptAllBtn' => 'Zaakceptuj wszystkie',
        'acceptNecessaryBtn' => 'Odrzuć wszystkie',
        'savePreferencesBtn' => 'Zapisz preferencje',
        'closeIconLabel' => 'Zamknij',
        'sections' => [
            [
                'title' => 'Wykorzystanie plików cookies',
                'description' => 'Wykorzystujemy pliki cookies, aby zapewnić podstawowe funkcje i poprawić Twoje doświadczenia.',
            ],
            [
                'title' => 'Ściśle niezbędne pliki cookies',
                'description' => 'Niezbędne do działania strony. Zawsze aktywne.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Pliki cookies analityczne',
                'description' => 'Pomagają nam zrozumieć, jak odwiedzający korzystają ze strony.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
