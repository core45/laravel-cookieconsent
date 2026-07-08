<?php

return [
    'consentModal' => [
        'title' => 'We use cookies',
        'description' => 'We use cookies to ensure basic site functionality and to analyse our traffic. You decide which categories you allow.',
        'acceptAllBtn' => 'Accept all',
        'acceptNecessaryBtn' => 'Reject all',
        'showPreferencesBtn' => 'Manage preferences',
    ],
    'preferencesModal' => [
        'title' => 'Cookie preferences',
        'acceptAllBtn' => 'Accept all',
        'acceptNecessaryBtn' => 'Reject all',
        'savePreferencesBtn' => 'Save preferences',
        'closeIconLabel' => 'Close',
        'sections' => [
            [
                'title' => 'Cookie usage',
                'description' => 'We use cookies to provide basic functionality and improve your experience.',
            ],
            [
                'title' => 'Strictly necessary cookies',
                'description' => 'Essential for the website to function. Always active.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Analytics cookies',
                'description' => 'Help us understand how visitors use the website.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
