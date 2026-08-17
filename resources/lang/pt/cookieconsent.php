<?php

return [
    'consentModal' => [
        'title' => 'Utilizamos cookies',
        'description' => 'Utilizamos cookies para garantir as funcionalidades básicas do site e analisar o nosso tráfego. É o utilizador que decide quais as categorias que permite.',
        'acceptAllBtn' => 'Aceitar todos',
        'acceptNecessaryBtn' => 'Rejeitar todos',
        'showPreferencesBtn' => 'Gerir preferências',
    ],
    'preferencesModal' => [
        'title' => 'Preferências de cookies',
        'acceptAllBtn' => 'Aceitar todos',
        'acceptNecessaryBtn' => 'Rejeitar todos',
        'savePreferencesBtn' => 'Guardar preferências',
        'closeIconLabel' => 'Fechar',
        'sections' => [
            [
                'title' => 'Utilização de cookies',
                'description' => 'Utilizamos cookies para fornecer funcionalidades básicas e melhorar a sua experiência.',
            ],
            [
                'title' => 'Cookies estritamente necessários',
                'description' => 'Essenciais para o funcionamento do site. Sempre ativos.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Cookies de análise',
                'description' => 'Ajudam-nos a perceber como os visitantes utilizam o site.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
