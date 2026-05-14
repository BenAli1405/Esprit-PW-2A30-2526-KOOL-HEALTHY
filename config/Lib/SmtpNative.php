<?php
declare(strict_types=1);

/**
 * Envoi SMTP minimal (AUTH LOGIN + STARTTLS) sans bibliotheque externe.
 */
final class SmtpNative {

    public static function send(
        string $host,
        int $port,
        string $username,
        string $password,
        string $secure,
        string $fromEmail,
        string $fromName,
        string $toEmail,
        string $subject,
        string $plainBody
    ): bool {
        try {
            return self::sendUnchecked($host, $port, $username, $password, $secure, $fromEmail, $fromName, $toEmail, $subject, $plainBody);
        } catch (Throwable $e) {
            return false;
        }
    }

    private static function sendUnchecked(
        string $host,
        int $port,
        string $username,
        string $password,
        string $secure,
        string $fromEmail,
        string $fromName,
        string $toEmail,
        string $subject,
        string $plainBody
    ): bool {
        $secure = strtolower($secure);
        if ($port === 465 && $secure === 'ssl') {
            $remote = 'ssl://' . $host . ':465';
            $useStartTls = false;
        } else {
            $remote = 'tcp://' . $host . ':' . $port;
            $useStartTls = ($port === 587);
        }
        $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
        $fp = @stream_socket_client($remote, $errno, $errstr, 25, STREAM_CLIENT_CONNECT, $ctx);
        if (!is_resource($fp)) {
            return false;
        }
        stream_set_timeout($fp, 30);
        self::expect($fp, [220]);
        self::cmd($fp, 'EHLO kool-healthy');
        self::expect($fp, [250]);
        if ($useStartTls) {
            self::cmd($fp, 'STARTTLS');
            self::expect($fp, [220]);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);

                return false;
            }
            self::cmd($fp, 'EHLO kool-healthy');
            self::expect($fp, [250]);
        }
        self::cmd($fp, 'AUTH LOGIN');
        self::expect($fp, [334]);
        self::cmd($fp, base64_encode($username));
        self::expect($fp, [334]);
        self::cmd($fp, base64_encode($password));
        self::expect($fp, [235]);
        self::cmd($fp, 'MAIL FROM:<' . $fromEmail . '>');
        self::expect($fp, [250]);
        self::cmd($fp, 'RCPT TO:<' . $toEmail . '>');
        self::expect($fp, [250, 251]);
        self::cmd($fp, 'DATA');
        self::expect($fp, [354]);
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $fromHdr = self::encodeMimeWord($fromName) . ' <' . $fromEmail . '>';
        $data = "From: {$fromHdr}\r\n";
        $data .= "To: <{$toEmail}>\r\n";
        $data .= "Subject: {$encodedSubject}\r\n";
        $data .= "MIME-Version: 1.0\r\n";
        $data .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $data .= "Content-Transfer-Encoding: 8bit\r\n";
        $data .= "\r\n";
        $data .= str_replace("\n.", "\n..", str_replace(["\r\n", "\r"], "\n", $plainBody));
        $data = str_replace("\n", "\r\n", $data);
        $data .= "\r\n.\r\n";
        fwrite($fp, $data);
        self::expect($fp, [250]);
        self::cmd($fp, 'QUIT');
        fclose($fp);

        return true;
    }


    private static function encodeMimeWord(string $name): string {
        if (preg_match('/[^\x20-\x7E]/', $name)) {
            return '=?UTF-8?B?' . base64_encode($name) . '?=';
        }

        return $name;
    }

    private static function cmd($fp, string $line): void {
        fwrite($fp, $line . "\r\n");
    }

    /** @param int[] $codes */
    private static function expect($fp, array $codes): void {
        $line = fgets($fp);
        if ($line === false) {
            throw new RuntimeException('SMTP: lecture vide');
        }
        $code = (int) substr($line, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new RuntimeException('SMTP inattendu: ' . trim($line));
        }
        while (strlen($line) >= 4 && $line[3] === '-') {
            $line = fgets($fp);
            if ($line === false) {
                break;
            }
        }
    }
}
