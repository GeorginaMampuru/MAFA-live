<?php
/**
 * Template only. Production uses config.php in this folder (upload with the API).
 */
return [
    // Browser origins allowed to call this API (include your dev server and production URLs).
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://mafaaccounting.co.za',
        'http://www.mafaaccounting.co.za',
        'https://mafaaccounting.co.za',
        'https://www.mafaaccounting.co.za',
    ],
    // Where the live site serves /images/mafa-logo.png (used in confirmation emails).
    'site_public_url' => 'https://www.mafaaccounting.co.za',
    // Internal notifications + From address for auto-replies (must be allowed by your host).
    'from_email' => 'info@mafaaccounting.co.za',
    'from_name' => 'MAFA Accounting Services & Advisory',
    'admin_email' => 'info@mafaaccounting.co.za',
];
