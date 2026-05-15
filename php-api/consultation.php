<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/lib/db.php';
require __DIR__ . '/lib/mail.php';

mafa_cors();
mafa_options_bail();
mafa_require_post();
mafa_assert_allowed_origin();

const MAFA_TIME_SLOTS = ['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00'];
const MAFA_METHOD_LABELS = [
    'phone' => 'Phone call',
    'office' => 'In-person (Randburg)',
];

$j = mafa_read_json_body();
$name = mafa_str($j['name'] ?? '', 200);
$email = mafa_str($j['email'] ?? '', 254);
$phone = mafa_str($j['phone'] ?? '', 80);
$service = mafa_str($j['service'] ?? '', 200);
$method = mafa_str($j['method'] ?? '', 40);
$date = mafa_str($j['date'] ?? '', 32);
$time = mafa_str($j['time'] ?? '', 16);
$notes = mafa_str($j['notes'] ?? '', 2000);

if ($name === '' || !mafa_valid_email($email) || $phone === '') {
    mafa_json(400, ['ok' => false, 'error' => 'Please provide your name, email, and phone number.']);
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    mafa_json(400, ['ok' => false, 'error' => 'Please choose a valid appointment date.']);
}

$d = DateTimeImmutable::createFromFormat('Y-m-d', $date);
$today = new DateTimeImmutable('today');
if ($d === false || $d < $today) {
    mafa_json(400, ['ok' => false, 'error' => 'Appointment date must be today or in the future.']);
}

if ($d->format('w') === '0') {
    mafa_json(400, ['ok' => false, 'error' => 'Sunday is not available for appointments.']);
}

if (!in_array($time, MAFA_TIME_SLOTS, true)) {
    mafa_json(400, ['ok' => false, 'error' => 'Please choose a valid time slot.']);
}

if (!isset(MAFA_METHOD_LABELS[$method])) {
    mafa_json(400, ['ok' => false, 'error' => 'Please choose a contact method.']);
}

$methodLabel = MAFA_METHOD_LABELS[$method];
$dateLabel = $d->format('l, j F Y');

mafa_db_require_or_fail();
if (
    mafa_db_enabled()
    && !mafa_db_insert_consultation(
        $name,
        $email,
        $phone,
        $service,
        $method,
        $date,
        $time,
        $notes,
    )
) {
    mafa_json(500, ['ok' => false, 'error' => 'We could not save your booking. Please call 072 145 0792 or email us directly.']);
}

$cfg = mafa_config();
$admin = (string) ($cfg['admin_email'] ?? 'info@mafaaccounting.co.za');
$from = (string) ($cfg['from_email'] ?? $admin);

$adminInner = '<h2 style="margin:0 0 16px;font-size:20px;color:#0f172a;">New consultation / appointment request</h2>'
    . '<table style="width:100%;font-size:14px;color:#334155;border-collapse:collapse;">'
    . '<tr><td style="padding:8px 0;font-weight:600;width:160px;vertical-align:top;">Name</td><td style="padding:8px 0;">' . mafa_h($name) . '</td></tr>'
    . '<tr><td style="padding:8px 0;font-weight:600;vertical-align:top;">Email</td><td style="padding:8px 0;"><a href="mailto:' . mafa_h($email) . '">' . mafa_h($email) . '</a></td></tr>'
    . '<tr><td style="padding:8px 0;font-weight:600;vertical-align:top;">Phone</td><td style="padding:8px 0;">' . mafa_h($phone) . '</td></tr>'
    . '<tr><td style="padding:8px 0;font-weight:600;vertical-align:top;">Service</td><td style="padding:8px 0;">' . mafa_h($service !== '' ? $service : 'General consultation') . '</td></tr>'
    . '<tr><td style="padding:8px 0;font-weight:600;vertical-align:top;">Preferred date</td><td style="padding:8px 0;">' . mafa_h($dateLabel) . '</td></tr>'
    . '<tr><td style="padding:8px 0;font-weight:600;vertical-align:top;">Preferred time</td><td style="padding:8px 0;">' . mafa_h($time) . '</td></tr>'
    . '<tr><td style="padding:8px 0;font-weight:600;vertical-align:top;">Method</td><td style="padding:8px 0;">' . mafa_h($methodLabel) . '</td></tr>'
    . '<tr><td style="padding:8px 0;font-weight:600;vertical-align:top;">Notes</td><td style="padding:8px 0;line-height:1.6;">' . ($notes !== '' ? nl2br(mafa_h($notes)) : '—') . '</td></tr>'
    . '</table>';

$userInner = '<h2 style="margin:0 0 12px;font-size:20px;color:#0f172a;">Your consultation request is received</h2>'
    . '<p style="line-height:1.65;color:#334155;margin:0 0 12px;">Hi <strong>' . mafa_h($name) . '</strong>,</p>'
    . '<p style="line-height:1.65;color:#334155;margin:0 0 12px;">Thank you for booking a consultation with MAFA Accounting Services &amp; Advisory. Below is a summary of what you requested. We will confirm your appointment within <strong>one business day</strong>.</p>'
    . '<table style="width:100%;font-size:14px;color:#334155;margin-top:8px;border-collapse:collapse;background:#f8fafc;border-radius:8px;">'
    . '<tr><td style="padding:8px 12px;font-weight:600;">Date</td><td style="padding:8px 12px;">' . mafa_h($dateLabel) . '</td></tr>'
    . '<tr><td style="padding:8px 12px;font-weight:600;">Time</td><td style="padding:8px 12px;">' . mafa_h($time) . '</td></tr>'
    . '<tr><td style="padding:8px 12px;font-weight:600;">Method</td><td style="padding:8px 12px;">' . mafa_h($methodLabel) . '</td></tr>'
    . '<tr><td style="padding:8px 12px;font-weight:600;">Service</td><td style="padding:8px 12px;">' . mafa_h($service !== '' ? $service : 'General consultation') . '</td></tr>'
    . '</table>'
    . '<p style="line-height:1.65;color:#334155;margin:16px 0 0;">Questions? Reply to this email or call <strong>072 145 0792</strong>.</p>';

$okAdmin = mafa_mail_html($admin, 'Appointment request: ' . $name, mafa_wrap_html($adminInner), $email);
$okUser = mafa_mail_html($email, 'We received your consultation request — MAFA Accounting', mafa_wrap_html($userInner), $from);

if (!$okAdmin || !$okUser) {
    mafa_json(502, ['ok' => false, 'error' => 'We could not send the confirmation email. Please call 072 145 0792 or email us directly.']);
}

mafa_json(200, ['ok' => true]);
