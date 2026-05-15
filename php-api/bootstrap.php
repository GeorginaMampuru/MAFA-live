<?php
declare(strict_types=1);

$configPath = __DIR__ . '/config.php';
if (!is_file($configPath)) {
    $configPath = __DIR__ . '/config.example.php';
}
if (!is_file($configPath)) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok' => false,
        'error' => 'Server mail is not configured. Add config.php (copy from config.example.php) or keep config.example.php in the php-api folder.',
    ]);
    exit;
}

/** @var array<string, mixed> $config */
$config = require $configPath;

function mafa_config(): array
{
    global $config;
    return $config;
}

function mafa_json(int $status, array $body): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($body);
    exit;
}

function mafa_h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function mafa_cors(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowed = mafa_config()['allowed_origins'] ?? [];
    if (!is_array($allowed)) {
        return;
    }
    if ($origin !== '' && in_array($origin, $allowed, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Max-Age: 86400');
    }
}

function mafa_options_bail(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function mafa_require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        mafa_json(405, ['ok' => false, 'error' => 'Method not allowed']);
    }
}

function mafa_assert_allowed_origin(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowed = mafa_config()['allowed_origins'] ?? [];
    if (!is_array($allowed) || $origin === '' || !in_array($origin, $allowed, true)) {
        mafa_json(403, ['ok' => false, 'error' => 'Forbidden']);
    }
}

/** @return array<string, mixed> */
function mafa_read_json_body(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function mafa_str(mixed $v, int $max = 5000): string
{
    if (!is_string($v)) {
        return '';
    }
    $t = trim($v);
    if (strlen($t) > $max) {
        return substr($t, 0, $max);
    }
    return $t;
}

function mafa_valid_email(string $e): bool
{
    return filter_var($e, FILTER_VALIDATE_EMAIL) !== false;
}
