<?php
require_once __DIR__ . '/../core/db.php';

function topic_fetch_all($limit = 50, $offset = 0): array
{
    // Placeholder data - returns two sample topics
    return [
        [
            'id' => 1,
            'title' => 'Nová expanze World of Warcraft',
            'body' => 'Blizzard právě oznámil novou expanzi pro World of Warcraft! Zaměří se na dračí aspekty a přinese nové zóny, dungeony a raid. Beta testování začne příští měsíc. Co si o tom myslíte?',
            'created_at' => '2025-10-20 14:30:00',
            'user_id' => 1
        ],
        [
            'id' => 2,
            'title' => 'Turnaj v CS2 - Registrace otevřena',
            'body' => 'Ahoj všichni! Organizujeme komunitní turnaj v Counter-Strike 2. Registrace týmů je nyní otevřená. Termín konání: 5. listopadu. Ceny pro top 3 týmy. Přihlaste se na našem Discordu!',
            'created_at' => '2025-10-21 09:15:00',
            'user_id' => 1
        ]
    ];
}

function topic_create($user_id, $title, $body): bool
{
    // temporarily return true so you can test
    return true;
}
