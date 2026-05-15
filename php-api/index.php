<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'ok' => true,
    'message' => 'MAFA form API. Use POST contact.php or consultation.php from the website.',
]);
