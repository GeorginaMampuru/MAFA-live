<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'ok' => true,
    'service' => 'mafa-php-api',
    'endpoints' => ['POST /contact.php', 'POST /consultation.php'],
]);
