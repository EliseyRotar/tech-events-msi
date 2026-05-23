<?php

namespace App;

/**
 * Sends email via Gmail SMTP using cURL.
 * Configure via environment variables:
 *   MAIL_FROM       — Gmail address (e.g. techdragonevents@gmail.com)
 *   MAIL_APP_PASS   — Gmail App Password (16 chars, spaces optional)
 *   MAIL_FROM_NAME  — Display name shown in email clients
 *   CONTACT_EMAIL   — Where contact form submissions are delivered
 */
class Mailer
{
    public static function send(string $to, string $subject, string $text, ?string $html = null): bool
    {
        $from = getenv('MAIL_FROM') ?: '';
        $pass = str_replace(' ', '', getenv('MAIL_APP_PASS') ?: '');
        $name = getenv('MAIL_FROM_NAME') ?: 'Tech Dragons Events';

        if ($from === '' || $pass === '') {
            error_log('[Mailer] MAIL_FROM or MAIL_APP_PASS not configured');
            return false;
        }

        // Build RFC 2822 MIME message
        $boundary = '=_Part_' . md5(uniqid('', true));

        $headers = "From: {$name} <{$from}>\r\n"
                 . "To: {$to}\r\n"
                 . "Subject: {$subject}\r\n"
                 . "MIME-Version: 1.0\r\n";

        if ($html !== null) {
            $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
            $body     = "\r\n--{$boundary}\r\n"
                      . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
                      . $text . "\r\n"
                      . "--{$boundary}\r\n"
                      . "Content-Type: text/html; charset=UTF-8\r\n\r\n"
                      . $html . "\r\n"
                      . "--{$boundary}--\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $body     = "\r\n" . $text . "\r\n";
        }

        $message = $headers . $body;
        $pos     = 0;

        $ch = curl_init('smtps://smtp.gmail.com:465');
        curl_setopt_array($ch, [
            CURLOPT_MAIL_FROM      => "<{$from}>",
            CURLOPT_MAIL_RCPT      => ["<{$to}>"],
            CURLOPT_USERPWD        => "{$from}:{$pass}",
            CURLOPT_USE_SSL        => CURLUSESSL_ALL,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_UPLOAD         => true,
            CURLOPT_READFUNCTION   => static function ($ch, $fd, $len) use (&$message, &$pos) {
                $chunk = substr($message, $pos, $len);
                $pos  += strlen($chunk);
                return $chunk;
            },
            CURLOPT_INFILESIZE     => strlen($message),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_VERBOSE        => false,
        ]);

        curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err !== '') {
            error_log("[Mailer] Gmail SMTP error: {$err}");
            return false;
        }

        return true;
    }
}
