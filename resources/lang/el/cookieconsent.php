<?php

return [
    'consentModal' => [
        'title' => 'Χρησιμοποιούμε cookies',
        'description' => 'Χρησιμοποιούμε cookies για να εξασφαλίσουμε τη βασική λειτουργία του ιστότοπου και να αναλύσουμε την κίνηση. Εσείς αποφασίζετε ποιες κατηγορίες επιτρέπετε.',
        'acceptAllBtn' => 'Αποδοχή όλων',
        'acceptNecessaryBtn' => 'Απόρριψη όλων',
        'showPreferencesBtn' => 'Διαχείριση προτιμήσεων',
    ],
    'preferencesModal' => [
        'title' => 'Προτιμήσεις cookies',
        'acceptAllBtn' => 'Αποδοχή όλων',
        'acceptNecessaryBtn' => 'Απόρριψη όλων',
        'savePreferencesBtn' => 'Αποθήκευση προτιμήσεων',
        'closeIconLabel' => 'Κλείσιμο',
        'sections' => [
            [
                'title' => 'Χρήση cookies',
                'description' => 'Χρησιμοποιούμε cookies για να παρέχουμε βασική λειτουργία και να βελτιώσουμε την εμπειρία σας.',
            ],
            [
                'title' => 'Αυστηρά απαραίτητα cookies',
                'description' => 'Απαραίτητα για τη λειτουργία του ιστότοπου. Πάντα ενεργά.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Αναλυτικά cookies',
                'description' => 'Μας βοηθούν να κατανοήσουμε πώς οι επισκέπτες χρησιμοποιούν τον ιστότοπο.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
