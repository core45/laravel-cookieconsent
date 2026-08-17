<?php

return [
    'consentModal' => [
        'title' => 'Ne përdorim cookie',
        'description' => 'Ne përdorim cookie për të siguruar funksionalitetin bazë të faqes dhe për të analizuar trafikun. Ti vendos se cilat kategori i lejon.',
        'acceptAllBtn' => 'Prano të gjitha',
        'acceptNecessaryBtn' => 'Refuzo të gjitha',
        'showPreferencesBtn' => 'Menaxho preferencat',
    ],
    'preferencesModal' => [
        'title' => 'Preferencat e cookie',
        'acceptAllBtn' => 'Prano të gjitha',
        'acceptNecessaryBtn' => 'Refuzo të gjitha',
        'savePreferencesBtn' => 'Ruaj preferencat',
        'closeIconLabel' => 'Mbyll',
        'sections' => [
            [
                'title' => 'Përdorimi i cookie',
                'description' => 'Ne përdorim cookie për të ofruar funksionalitetin bazë dhe për të përmirësuar përvojën tënde.',
            ],
            [
                'title' => 'Cookie rreptësisht të nevojshme',
                'description' => 'Të nevojshme që faqja e internetit të funksionojë. Gjithmonë aktive.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Cookie analitike',
                'description' => 'Na ndihmojnë të kuptojmë se si vizitorët përdorin faqen e internetit.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
