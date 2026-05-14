<?php
declare(strict_types=1);

require_once __DIR__ . '/SmtpNative.php';

/**
 * Envoi e-mail : SMTP natif (sans Composer) si smtp_host est configure, sinon mail().
 */
class MailSender {

    public static function send(string $toEmail, string $subject, string $plainBody): bool {
        $config = require dirname(__DIR__) . '/kool_config.php';
        $from = (string) ($config['mail_from'] ?? 'noreply@localhost');
        $fromName = (string) ($config['mail_from_name'] ?? 'Kool Healthy');
        $host = trim((string) ($config['smtp_host'] ?? ''));

        $ok = false;
        $error = '';

        if ($host !== '') {
            $ok = SmtpNative::send(
                $host,
                (int) ($config['smtp_port'] ?? 587),
                (string) ($config['smtp_user'] ?? ''),
                (string) ($config['smtp_pass'] ?? ''),
                strtolower((string) ($config['smtp_secure'] ?? 'tls')),
                $from,
                $fromName,
                $toEmail,
                $subject,
                $plainBody
            );
            if (!$ok) {
                $error = 'Echec envoi SMTP (verifiez hote, port, identifiants et openSSL).';
            }
        } else {
            $headers = [];
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $headers[] = 'From: ' . self::encodeHeaderName($fromName) . ' <' . $from . '>';
            $reply = trim((string) ($config['smtp_user'] ?? ''));
            if ($reply !== '' && filter_var($reply, FILTER_VALIDATE_EMAIL)) {
                $headers[] = 'Reply-To: ' . $reply;
            }
            $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $ok = @mail($toEmail, $encodedSubject, $plainBody, implode("\r\n", $headers));
            if (!$ok) {
                $error = 'La fonction mail() a retourne false (SMTP non configure ou sendmail absent).';
            }
        }

        if (!$ok) {
            self::logFailure($toEmail, $subject, $error, $config);
        }

        return $ok;
    }

    private static function encodeHeaderName(string $name): string {
        if (preg_match('/[^\x20-\x7E]/', $name)) {
            return '=?UTF-8?B?' . base64_encode($name) . '?=';
        }

        return $name;
    }

    private static function logFailure(string $to, string $subject, string $error, array $config): void {
        $path = (string) ($config['mail_log_path'] ?? '');
        if ($path === '') {
            return;
        }
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $line = date('c') . "\t" . $to . "\t" . $subject . "\t" . str_replace(["\r", "\n"], ' ', $error) . "\n";
        @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
    }
}
