<?php

return [
    'consentModal' => [
        'title' => 'Používáme soubory cookie',
        'description' => 'Používáme soubory cookie pro zajištění základních funkcí webu a pro analýzu naší návštěvnosti. Vy rozhodujete, které kategorie povolíte.',
        'acceptAllBtn' => 'Přijmout vše',
        'acceptNecessaryBtn' => 'Odmítnout vše',
        'showPreferencesBtn' => 'Spravovat nastavení',
    ],
    'preferencesModal' => [
        'title' => 'Nastavení souborů cookie',
        'acceptAllBtn' => 'Přijmout vše',
        'acceptNecessaryBtn' => 'Odmítnout vše',
        'savePreferencesBtn' => 'Uložit nastavení',
        'closeIconLabel' => 'Zavřít',
        'sections' => [
            [
                'title' => 'Používání souborů cookie',
                'description' => 'Používáme soubory cookie pro poskytování základních funkcí a zlepšení vašeho zážitku.',
            ],
            [
                'title' => 'Zásadně nezbytné soubory cookie',
                'description' => 'Nezbytné pro fungování webu. Vždy aktivní.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Analytické soubory cookie',
                'description' => 'Pomáhají nám pochopit, jak návštěvníci web používají.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
