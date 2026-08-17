<?php

return [
    'consentModal' => [
        'title' => 'Utilizziamo i cookie',
        'description' => 'Utilizziamo i cookie per garantire le funzionalità di base del sito e analizzare il nostro traffico. Tu decidi quali categorie consentire.',
        'acceptAllBtn' => 'Accetta tutti',
        'acceptNecessaryBtn' => 'Rifiuta tutti',
        'showPreferencesBtn' => 'Gestisci preferenze',
    ],
    'preferencesModal' => [
        'title' => 'Preferenze cookie',
        'acceptAllBtn' => 'Accetta tutti',
        'acceptNecessaryBtn' => 'Rifiuta tutti',
        'savePreferencesBtn' => 'Salva preferenze',
        'closeIconLabel' => 'Chiudi',
        'sections' => [
            [
                'title' => 'Utilizzo dei cookie',
                'description' => 'Utilizziamo i cookie per fornire funzionalità di base e migliorare la tua esperienza.',
            ],
            [
                'title' => 'Cookie strettamente necessari',
                'description' => 'Essenziali per il funzionamento del sito web. Sempre attivi.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Cookie analitici',
                'description' => 'Ci aiutano a capire come i visitatori utilizzano il sito web.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
