<?php

namespace App;

/**
 * Thin wrapper around the Resend email API.
 * No Composer dependencies — uses PHP's file_get_contents with stream context.
 *
 * Resend requires a verified domain for the FROM address.
 * We use 'onboarding@resend.dev' (pre-verified by Resend) as the technical sender.
 * MAIL_FROM / CONTACT_EMAIL is used for Reply-To, so replies land in your inbox.
 *
 * Configure via .env:
 *   RESEND_API_KEY    — from resend.com dashboard
 *   MAIL_FROM         — your email (used for Reply-To)
 *   MAIL_FROM_NAME    — display name shown in email clients
 *   CONTACT_EMAIL     — where contact form submissions are delivered
 */
class Mailer
{
    private const RESEND_ENDPOINT = 'https://api.resend.com/emails';
    private const RESEND_SENDER   = 'onboarding@resend.dev'; // Resend's pre-verified address

    private static function apiKey(): string
    {
        return getenv('RESEND_API_KEY') ?: '';
    }

    private static function displayName(): string
    {
        return getenv('MAIL_FROM_NAME') ?: 'Tech Dragons Events';
    }

    private static function replyTo(): string
    {
        return getenv('MAIL_FROM') ?: getenv('CONTACT_EMAIL') ?: '';
    }

    /**
     * Send a plain-text (and optional HTML) email via Resend.
     *
     * @param string      $to      Recipient address
     * @param string      $subject
     * @param string      $text    Plain-text body
     * @param string|null $html    Optional HTML body
     * @return bool                true on success
     */
    public static function send(string $to, string $subject, string $text, ?string $html = null): bool
    {
        $apiKey = self::apiKey();
        if ($apiKey === '') {
            error_log('[Mailer] RESEND_API_KEY not configured — email not sent');
            return false;
        }

        $from    = self::displayName() . ' <' . self::RESEND_SENDER . '>';
        $replyTo = self::replyTo();

        $payload = [
            'from'    => $from,
            'to'      => [$to],
            'subject' => $subject,
            'text'    => $text,
        ];

        if ($html !== null) {
            $payload['html'] = $html;
        }

        if ($replyTo !== '') {
            $payload['reply_to'] = [$replyTo];
        }

        $ctx = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => implode("\r\n", [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                ]),
                'content'       => json_encode($payload),
                'timeout'       => 10,
                'ignore_errors' => true,
            ],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $result = @file_get_contents(self::RESEND_ENDPOINT, false, $ctx);

        if ($result === false) {
            error_log('[Mailer] Network error reaching Resend API');
            return false;
        }

        $response = json_decode($result, true);

        if (isset($response['id'])) {
            return true;
        }

        error_log('[Mailer] Resend error: ' . $result);
        return false;
    }
}
