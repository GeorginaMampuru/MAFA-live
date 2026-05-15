<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/lib/db.php';

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'ok' => true,
    'service' => 'mafa-php-api',
    'endpoints' => ['POST /contact.php', 'POST /consultation.php'],
    'database' => mafa_db_enabled()
        ? (mafa_db() instanceof PDO ? 'connected' : 'error')
        : 'disabled',
]);
