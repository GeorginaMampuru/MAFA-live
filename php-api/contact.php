<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/lib/mail.php';

mafa_cors();
mafa_options_bail();
mafa_require_post();
mafa_assert_allowed_origin();

$j = mafa_read_json_body();
$name = mafa_str($j['name'] ?? '', 200);
$email = mafa_str($j['email'] ?? '', 254);
$phone = mafa_str($j['phone'] ?? '', 80);
$service = mafa_str($j['service'] ?? '', 200);
$message = mafa_str($j['message'] ?? '', 4000);

if ($name === '' || !mafa_valid_email($email) || $message === '') {
    mafa_json(400, ['ok' => false, 'error' => 'Please provide your name, a valid email, and a message.']);
}

$cfg = mafa_config();
$admin = (string) ($cfg['admin_email'] ?? 'info@mafaaccounting.co.za');
$from = (string) ($cfg['from_email'] ?? $admin);

$adminInner = '<h2 style="margin:0 0 16px;font-size:20px;color:#0f172a;">New website enquiry</h2>'
    . '<table style="width:100%;font-size:14px;color:#334155;border-collapse:collapse;">'
    . '<tr><td style="padding:8px 0;font-weight:600;width:120px;vertical-align:top;">Name</td><td style="padding:8px 0;">' . mafa_h($name) . '</td></tr>'
    . '<tr><td style="padding:8px 0;font-weight:600;vertical-align:top;">Email</td><td style="padding:8px 0;"><a href="mailto:' . mafa_h($email) . '">' . mafa_h($email) . '</a></td></tr>'
    . '<tr><td style="padding:8px 0;font-weight:600;vertical-align:top;">Phone</td><td style="padding:8px 0;">' . mafa_h($phone !== '' ? $phone : '—') . '</td></tr>'
    . '<tr><td style="padding:8px 0;font-weight:600;vertical-align:top;">Service</td><td style="padding:8px 0;">' . mafa_h($service !== '' ? $service : '—') . '</td></tr>'
    . '<tr><td style="padding:8px 0;font-weight:600;vertical-align:top;">Message</td><td style="padding:8px 0;line-height:1.6;">' . nl2br(mafa_h($message)) . '</td></tr>'
    . '</table>';

$userInner = '<h2 style="margin:0 0 12px;font-size:20px;color:#0f172a;">We received your enquiry</h2>'
    . '<p style="line-height:1.65;color:#334155;margin:0 0 12px;">Hi <strong>' . mafa_h($name) . '</strong>,</p>'
    . '<p style="line-height:1.65;color:#334155;margin:0 0 12px;">Thank you for contacting MAFA Accounting Services &amp; Advisory. This email confirms we have your message and will get back to you within <strong>one business day</strong>.</p>'
    . '<p style="line-height:1.65;color:#334155;margin:0;">For urgent matters, call <strong>072 145 0792</strong> or reply to this email.</p>';

$okAdmin = mafa_mail_html($admin, 'Website enquiry: ' . $name, mafa_wrap_html($adminInner), $email);
$okUser = mafa_mail_html($email, 'We received your enquiry — MAFA Accounting', mafa_wrap_html($userInner), $from);

if (!$okAdmin || !$okUser) {
    mafa_json(502, ['ok' => false, 'error' => 'We could not send the confirmation email. Please call 072 145 0792 or email us directly.']);
}

mafa_json(200, ['ok' => true]);
