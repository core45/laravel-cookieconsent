<?php

return [
    'consentModal' => [
        'title' => 'Sütiket használunk',
        'description' => 'Sütiket használunk az alapvető oldalfunkciók biztosítására és a forgalom elemzésére. Te döntöd el, mely kategóriákat engedélyezed.',
        'acceptAllBtn' => 'Összes elfogadása',
        'acceptNecessaryBtn' => 'Összes elutasítása',
        'showPreferencesBtn' => 'Beállítások kezelése',
    ],
    'preferencesModal' => [
        'title' => 'Süti beállítások',
        'acceptAllBtn' => 'Összes elfogadása',
        'acceptNecessaryBtn' => 'Összes elutasítása',
        'savePreferencesBtn' => 'Beállítások mentése',
        'closeIconLabel' => 'Bezárás',
        'sections' => [
            [
                'title' => 'Sütik használata',
                'description' => 'Sütiket használunk az alapvető funkciók biztosítására és a felhasználói élmény javítására.',
            ],
            [
                'title' => 'Szigorúan szükséges sütik',
                'description' => 'Elengedhetetlenek a weboldal működéséhez. Mindig aktívak.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Analitikai sütik',
                'description' => 'Segítenek megérteni, hogyan használják a látogatók a weboldalt.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
