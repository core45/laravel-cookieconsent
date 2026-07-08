<?php

return [
    'consentModal' => [
        'title' => 'Utilizamos cookies',
        'description' => 'Utilizamos cookies para garantizar las funciones básicas del sitio y analizar nuestro tráfico. Tú decides qué categorías permites.',
        'acceptAllBtn' => 'Aceptar todas',
        'acceptNecessaryBtn' => 'Rechazar todas',
        'showPreferencesBtn' => 'Gestionar preferencias',
    ],
    'preferencesModal' => [
        'title' => 'Preferencias de cookies',
        'acceptAllBtn' => 'Aceptar todas',
        'acceptNecessaryBtn' => 'Rechazar todas',
        'savePreferencesBtn' => 'Guardar preferencias',
        'closeIconLabel' => 'Cerrar',
        'sections' => [
            [
                'title' => 'Uso de cookies',
                'description' => 'Utilizamos cookies para ofrecer funciones básicas y mejorar tu experiencia.',
            ],
            [
                'title' => 'Cookies estrictamente necesarias',
                'description' => 'Imprescindibles para el funcionamiento del sitio. Siempre activas.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Cookies de análisis',
                'description' => 'Nos ayudan a entender cómo se usa el sitio web.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
