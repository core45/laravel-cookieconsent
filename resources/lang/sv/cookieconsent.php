<?php

return [
    'consentModal' => [
        'title' => 'Vi använder cookies',
        'description' => 'Vi använder cookies för att säkerställa grundläggande sidfunktioner och analysera vår trafik. Du bestämmer vilka kategorier du tillåter.',
        'acceptAllBtn' => 'Acceptera alla',
        'acceptNecessaryBtn' => 'Avvisa alla',
        'showPreferencesBtn' => 'Hantera inställningar',
    ],
    'preferencesModal' => [
        'title' => 'Cookieinställningar',
        'acceptAllBtn' => 'Acceptera alla',
        'acceptNecessaryBtn' => 'Avvisa alla',
        'savePreferencesBtn' => 'Spara inställningar',
        'closeIconLabel' => 'Stäng',
        'sections' => [
            [
                'title' => 'Användning av cookies',
                'description' => 'Vi använder cookies för att tillhandahålla grundläggande funktioner och förbättra din upplevelse.',
            ],
            [
                'title' => 'Strikt nödvändiga cookies',
                'description' => 'Väsentliga för att webbplatsen ska fungera. Alltid aktiva.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Analyscookies',
                'description' => 'Hjälp oss att förstå hur besökare använder webbplatsen.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
