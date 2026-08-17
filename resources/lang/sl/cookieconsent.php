<?php

return [
    'consentModal' => [
        'title' => 'Uporabljamo piškotke',
        'description' => 'Piškotke uporabljamo za zagotavljanje osnovnih funkcij spletnega mesta in analizo prometa. Vi odločate, katere kategorije dovolite.',
        'acceptAllBtn' => 'Sprejmi vse',
        'acceptNecessaryBtn' => 'Zavrni vse',
        'showPreferencesBtn' => 'Upravljanje nastavitev',
    ],
    'preferencesModal' => [
        'title' => 'Nastavitve piškotkov',
        'acceptAllBtn' => 'Sprejmi vse',
        'acceptNecessaryBtn' => 'Zavrni vse',
        'savePreferencesBtn' => 'Shrani nastavitve',
        'closeIconLabel' => 'Zapri',
        'sections' => [
            [
                'title' => 'Uporaba piškotkov',
                'description' => 'Piškotke uporabljamo za zagotavljanje osnovnih funkcij in izboljšanje vašega doživetja.',
            ],
            [
                'title' => 'Strogo nujni piškotki',
                'description' => 'Nujni za delovanje spletnega mesta. Vedno aktivni.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Analitični piškotki',
                'description' => 'Pomagajo nam razumeti, kako obiskovalci uporabljajo spletno mesto.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
