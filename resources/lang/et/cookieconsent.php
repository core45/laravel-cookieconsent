<?php

return [
    'consentModal' => [
        'title' => 'Kasutame küpsiseid',
        'description' => 'Kasutame küpsiseid, et tagada saidi põhitöövõime ja analüüsida liiklust. Sa otsustad, millised kategooriad sa lubad.',
        'acceptAllBtn' => 'Nõustu kõigiga',
        'acceptNecessaryBtn' => 'Keeldu kõigist',
        'showPreferencesBtn' => 'Halda eelistusi',
    ],
    'preferencesModal' => [
        'title' => 'Küpsiste eelistused',
        'acceptAllBtn' => 'Nõustu kõigiga',
        'acceptNecessaryBtn' => 'Keeldu kõigist',
        'savePreferencesBtn' => 'Salvesta eelistused',
        'closeIconLabel' => 'Sulge',
        'sections' => [
            [
                'title' => 'Küpsiste kasutamine',
                'description' => 'Kasutame küpsiseid, et pakkuda põhitöövõimet ja parandada sinu kogemust.',
            ],
            [
                'title' => 'Hädavajalikud küpsised',
                'description' => 'Vajalikud veebisaidi toimimiseks. Alati aktiivsed.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Analüütikaküpsised',
                'description' => 'Aitavad meil mõista, kuidas külastajad veebisaiti kasutavad.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
