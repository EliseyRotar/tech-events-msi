<?php

namespace App;

/**
 * Sends transactional email via Brevo (formerly Sendinblue) HTTP API.
 * Works on Render free tier (no SMTP needed — pure HTTPS on port 443).
 *
 * Required environment variables:
 *   BREVO_API_KEY   — Brevo API key (starts with xkeysib-)
 *   MAIL_FROM       — Verified sender email (e.g. techdragonevents@gmail.com)
 *   MAIL_FROM_NAME  — Display name shown in email clients
 *   CONTACT_EMAIL   — Where contact form submissions are delivered
 */
class Mailer
{
    public static function send(string $to, string $subject, string $text, ?string $html = null): bool
    {
        $apiKey  = getenv('BREVO_API_KEY') ?: '';
        $from    = getenv('MAIL_FROM') ?: '';
        $name    = getenv('MAIL_FROM_NAME') ?: 'Tech Dragons Events';

        if ($apiKey === '' || $from === '') {
            error_log('[Mailer] BREVO_API_KEY or MAIL_FROM not configured');
            return false;
        }

        $payload = [
            'sender'      => ['name' => $name, 'email' => $from],
            'to'          => [['email' => $to]],
            'subject'     => $subject,
            'textContent' => $text,
        ];

        if ($html !== null) {
            $payload['htmlContent'] = $html;
        }

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
                "api-key: {$apiKey}",
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err !== '') {
            error_log("[Mailer] cURL error: {$err}");
            return false;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("[Mailer] Brevo API error {$httpCode}: {$response}");
            return false;
        }

        return true;
    }
}
