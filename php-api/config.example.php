<?php
/**
 * Copy this file to config.php on the server and adjust values.
 * Do not commit config.php (it is gitignored).
 *
 * If the site is on Netlify, add your exact site URL to allowed_origins, e.g.
 * https://your-site-name.netlify.app (browsers send this as the Origin header).
 */
return [
    // Browser origins allowed to call this API (include your dev server and production URLs).
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
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
