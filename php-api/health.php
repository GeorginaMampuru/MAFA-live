<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/lib/db.php';

header('Content-Type: application/json; charset=utf-8');

$dbStatus = mafa_db_enabled()
    ? (mafa_db() instanceof PDO ? 'connected' : 'error')
    : 'disabled';

$payload = [
    'ok' => true,
    'service' => 'mafa-php-api',
    'endpoints' => ['POST /contact.php', 'POST /consultation.php'],
    'database' => $dbStatus,
];

if ($dbStatus === 'error') {
    $hint = mafa_db_last_hint();
    $payload['database_hint'] = $hint ?? 'connection_failed';
    $payload['database_fix'] = match ($hint) {
        'access_denied' => 'Wrong MySQL username or password in php-api/config.php, or user not added to the database in cPanel.',
        'unknown_database' => 'Database name in config.php does not match cPanel (use the full prefixed name).',
        'connection_refused' => 'Try host 127.0.0.1 instead of localhost in config.php db.host.',
        default => 'Check cPanel MySQL: user linked to DB with ALL PRIVILEGES; re-upload config.php with the exact password.',
    };
}

echo json_encode($payload);
