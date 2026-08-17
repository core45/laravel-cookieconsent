<?php

return [
    'consentModal' => [
        'title' => 'Ми використовуємо файли cookie',
        'description' => 'Ми використовуємо файли cookie, щоб забезпечити базові функції сайту та аналізувати наш трафік. Ви вирішуєте, які категорії дозволяти.',
        'acceptAllBtn' => 'Прийняти всі',
        'acceptNecessaryBtn' => 'Відхилити всі',
        'showPreferencesBtn' => 'Керувати налаштуваннями',
    ],
    'preferencesModal' => [
        'title' => 'Налаштування файлів cookie',
        'acceptAllBtn' => 'Прийняти всі',
        'acceptNecessaryBtn' => 'Відхилити всі',
        'savePreferencesBtn' => 'Зберегти налаштування',
        'closeIconLabel' => 'Закрити',
        'sections' => [
            [
                'title' => 'Використання файлів cookie',
                'description' => 'Ми використовуємо файли cookie, щоб забезпечити базові функції та покращити ваш досвід.',
            ],
            [
                'title' => 'Суто необхідні файли cookie',
                'description' => 'Необхідні для роботи сайту. Завжди активні.',
                'linkedCategory' => 'necessary',
            ],
            [
                'title' => 'Файли cookie аналітики',
                'description' => 'Допомагають нам зрозуміти, як відвідувачі використовують сайт.',
                'linkedCategory' => 'analytics',
            ],
        ],
    ],
];
