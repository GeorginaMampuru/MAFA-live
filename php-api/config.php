<?php
/**
 * Live settings for mafaaccounting.co.za — upload this file with the rest of php-api/.
 */
return [
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://mafaaccounting.co.za',
        'http://www.mafaaccounting.co.za',
        'https://mafaaccounting.co.za',
        'https://www.mafaaccounting.co.za',
    ],
    'site_public_url' => 'https://mafaaccounting.co.za',
    'from_email' => 'info@mafaaccounting.co.za',
    'from_name' => 'MAFA Accounting Services & Advisory',
    'admin_email' => 'info@mafaaccounting.co.za',

    // MySQL (phpMyAdmin) — set password after creating DB/user in cPanel
    'db' => [
        'host' => 'localhost',
        'name' => 'mafaacd4q0c2_mafa-consultation',
        'user' => 'mafaacd4q0c2_mafa-consultation',
        'password' => 'Mafa@78##',
        'charset' => 'utf8mb4',
    ],
];
