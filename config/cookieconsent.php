<?php

return [

    /*
    | Every key in this file except `translations_mode`, `csp_nonce`,
    | `logging` and `iframemanager` is passed straight through to
    | CookieConsent.run() — see https://cookieconsent.orestbida.com/reference/configuration-reference.html
    */

    'cookie' => [
        'name' => 'cc_cookie',
        'expiresAfterDays' => 182,
        // WARNING: enabling `useLocalStorage` disables server-side consent
        // reads (@consent, Consent::has(), consent:* middleware). See README.
    ],

    'mode' => 'opt-in',
    'revision' => 0,

    'guiOptions' => [
        'consentModal' => ['layout' => 'box', 'position' => 'bottom left'],
        'preferencesModal' => ['layout' => 'box'],
    ],

    'categories' => [
        'necessary' => ['enabled' => true, 'readOnly' => true],
        'analytics' => [
            'autoClear' => [
                'cookies' => [
                    // Use CookieConsent::regex('^_ga') for RegExp matching.
                    ['name' => '_gid'],
                ],
            ],
        ],
    ],

    // 'active' = only the current app locale; 'all' = every published locale.
    'translations_mode' => 'active',

    // null, or a callable returning the per-request CSP nonce,
    // e.g. fn () => \Illuminate\Support\Facades\Vite::cspNonce()
    'csp_nonce' => null,

    'logging' => [
        'enabled' => true,
        'csrf' => true,
        'capture_ip' => 'raw',            // 'raw' | 'hashed' | false
        'ip_hash_salt' => env('COOKIECONSENT_IP_SALT'),
        'capture_user_agent' => true,
        'link_user' => true,
        'morph_id_type' => 'int',         // 'int' | 'uuid' | 'ulid' | 'string'
        'policy_version' => null,
        'retention_days' => null,
        'rate_limit' => '30,1',
    ],

    'iframemanager' => [
        'enabled' => false,
        'services' => [],
    ],
];
