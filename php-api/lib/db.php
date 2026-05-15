<?php
declare(strict_types=1);

function mafa_db_enabled(): bool
{
    $db = mafa_config()['db'] ?? null;
    if (!is_array($db)) {
        return false;
    }
    $password = $db['password'] ?? '';
    $name = $db['name'] ?? '';
    $user = $db['user'] ?? '';
    return is_string($name) && $name !== ''
        && is_string($user) && $user !== ''
        && is_string($password) && $password !== '';
}

function mafa_db_classify_error(PDOException $e): string
{
    $msg = $e->getMessage();
    if (str_contains($msg, '1045') || stripos($msg, 'access denied') !== false) {
        return 'access_denied';
    }
    if (str_contains($msg, '1049') || stripos($msg, 'unknown database') !== false) {
        return 'unknown_database';
    }
    if (str_contains($msg, '2002') || stripos($msg, 'connection refused') !== false) {
        return 'connection_refused';
    }
    return 'connection_failed';
}

/** @return PDO|null */
function mafa_db()
{
    static $pdo = null;
    static $attempted = false;

    if ($pdo instanceof PDO) {
        return $pdo;
    }
    if ($attempted) {
        return null;
    }
    $attempted = true;

    if (!mafa_db_enabled()) {
        return null;
    }

    $db = mafa_config()['db'];
    $host = (string) ($db['host'] ?? 'localhost');
    $name = (string) ($db['name'] ?? '');
    $user = (string) ($db['user'] ?? '');
    $password = (string) ($db['password'] ?? '');
    $charset = (string) ($db['charset'] ?? 'utf8mb4');

    try {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $host, $name, $charset);
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log('MAFA DB connection failed: ' . $e->getMessage());
        $GLOBALS['mafa_db_hint'] = mafa_db_classify_error($e);
        return null;
    }
}

function mafa_db_last_hint(): ?string
{
    return isset($GLOBALS['mafa_db_hint']) && is_string($GLOBALS['mafa_db_hint'])
        ? $GLOBALS['mafa_db_hint']
        : null;
}

function mafa_client_meta(): array
{
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
    if (str_contains($ip, ',')) {
        $ip = trim(explode(',', $ip)[0]);
    }
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (strlen($ua) > 512) {
        $ua = substr($ua, 0, 512);
    }
    return [
        'ip' => mafa_str($ip, 45),
        'ua' => $ua,
    ];
}

function mafa_db_insert_contact(
    string $name,
    string $email,
    string $phone,
    string $service,
    string $message,
): bool {
    $pdo = mafa_db();
    if (!$pdo) {
        return false;
    }
    $meta = mafa_client_meta();
    $stmt = $pdo->prepare(
        'INSERT INTO contact_enquiries (name, email, phone, service, message, ip_address, user_agent)
         VALUES (:name, :email, :phone, :service, :message, :ip, :ua)',
    );
    return $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':service' => $service,
        ':message' => $message,
        ':ip' => $meta['ip'] !== '' ? $meta['ip'] : null,
        ':ua' => $meta['ua'] !== '' ? $meta['ua'] : null,
    ]);
}

function mafa_db_insert_consultation(
    string $name,
    string $email,
    string $phone,
    string $service,
    string $method,
    string $date,
    string $time,
    string $notes,
): bool {
    $pdo = mafa_db();
    if (!$pdo) {
        return false;
    }
    $meta = mafa_client_meta();
    $stmt = $pdo->prepare(
        'INSERT INTO consultation_requests
         (name, email, phone, service, method, preferred_date, preferred_time, notes, ip_address, user_agent)
         VALUES (:name, :email, :phone, :service, :method, :date, :time, :notes, :ip, :ua)',
    );
    return $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':service' => $service,
        ':method' => $method,
        ':date' => $date,
        ':time' => $time,
        ':notes' => $notes,
        ':ip' => $meta['ip'] !== '' ? $meta['ip'] : null,
        ':ua' => $meta['ua'] !== '' ? $meta['ua'] : null,
    ]);
}

function mafa_db_require_or_fail(): void
{
    if (!mafa_db_enabled()) {
        return;
    }
    if (mafa_db() instanceof PDO) {
        return;
    }
    mafa_json(500, [
        'ok' => false,
        'error' => 'Database is not available. Please call 072 145 0792 or email info@mafaaccounting.co.za.',
    ]);
}
