<?php
declare(strict_types=1);

function mafa_wrap_html(string $innerHtml): string
{
    $base = rtrim((string) (mafa_config()['site_public_url'] ?? ''), '/');
    $logoUrl = $base !== '' ? $base . '/images/mafa-logo.png' : '';

    $logoBlock = $logoUrl !== ''
        ? '<div style="text-align:center;padding:16px 0 24px;"><img src="' . mafa_h($logoUrl) . '" alt="MAFA Accounting" width="200" style="max-width:200px;height:auto;border:0;" /></div>'
        : '';

    return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
        . '<body style="margin:0;font-family:Georgia,&#039;Times New Roman&#039;,serif;background:#f4f6fa;color:#1a2744;">'
        . '<div style="max-width:560px;margin:0 auto;padding:32px 16px;">'
        . $logoBlock
        . '<div style="background:#ffffff;border-radius:12px;padding:28px 24px;box-shadow:0 8px 28px rgba(15,23,42,.08);">'
        . $innerHtml
        . '</div>'
        . '<p style="text-align:center;font-size:12px;color:#64748b;margin-top:24px;">MAFA Accounting Services &amp; Advisory · Johannesburg, South Africa</p>'
        . '</div></body></html>';
}

function mafa_mail_html(string $to, string $subject, string $htmlBody, ?string $replyTo = null): bool
{
    if (!mafa_valid_email($to)) {
        return false;
    }
    $cfg = mafa_config();
    $from = (string) ($cfg['from_email'] ?? 'info@mafaaccounting.co.za');
    $fromName = (string) ($cfg['from_name'] ?? 'MAFA Accounting');
    if (!mafa_valid_email($from)) {
        return false;
    }

    $subjectEnc = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'From: ' . mafa_encode_mail_name($fromName) . ' <' . $from . '>',
    ];
    if ($replyTo !== null && $replyTo !== '' && mafa_valid_email($replyTo)) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    return @mail($to, $subjectEnc, $htmlBody, implode("\r\n", $headers));
}

function mafa_encode_mail_name(string $name): string
{
    if (preg_match('/[^\x20-\x7E]/', $name)) {
        return '=?UTF-8?B?' . base64_encode($name) . '?=';
    }
    return '"' . addcslashes($name, '"\\') . '"';
}
