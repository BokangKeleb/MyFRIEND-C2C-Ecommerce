<?php

require_once __DIR__ . '/../config/mail.php';

function smtp_read_response($socket): string
{
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (strlen($line) < 4 || $line[3] === ' ') {
            break;
        }
    }
    return $response;
}

function smtp_command($socket, string $command, array $acceptedCodes): bool
{
    if ($command !== '') {
        fwrite($socket, $command . "\r\n");
    }
    $response = smtp_read_response($socket);
    $code = (int)substr($response, 0, 3);
    return in_array($code, $acceptedCodes, true);
}

function clean_mail_header(string $value): string
{
    return trim(str_replace(["\r", "\n"], '', $value));
}

function send_reset_email(string $toEmail, string $toName, string $resetUrl, string &$error = ''): bool
{
    if (MAIL_MODE === 'preview') {
        return true;
    }

    if (
        SMTP_HOST === '' || SMTP_USERNAME === '' || SMTP_PASSWORD === ''
        || SMTP_FROM_EMAIL === '' || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)
    ) {
        $error = 'SMTP email settings are incomplete in config/mail.php.';
        return false;
    }

    $host = SMTP_ENCRYPTION === 'ssl' ? 'ssl://' . SMTP_HOST : SMTP_HOST;
    $socket = @stream_socket_client(
        $host . ':' . SMTP_PORT,
        $errorNumber,
        $errorMessage,
        20,
        STREAM_CLIENT_CONNECT
    );

    if (!$socket) {
        $error = 'Could not connect to the email server: ' . $errorMessage;
        return false;
    }

    stream_set_timeout($socket, 20);

    if (!smtp_command($socket, '', [220])) {
        fclose($socket);
        $error = 'The email server did not respond correctly.';
        return false;
    }

    if (!smtp_command($socket, 'EHLO myfriend.local', [250])) {
        fclose($socket);
        $error = 'The email server rejected the connection.';
        return false;
    }

    if (SMTP_ENCRYPTION === 'tls') {
        if (!smtp_command($socket, 'STARTTLS', [220])) {
            fclose($socket);
            $error = 'The email server could not start a secure connection.';
            return false;
        }

        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            $error = 'The secure email connection could not be enabled.';
            return false;
        }

        if (!smtp_command($socket, 'EHLO myfriend.local', [250])) {
            fclose($socket);
            $error = 'The email server rejected the secure connection.';
            return false;
        }
    }

    if (!smtp_command($socket, 'AUTH LOGIN', [334])
        || !smtp_command($socket, base64_encode(SMTP_USERNAME), [334])
        || !smtp_command($socket, base64_encode(SMTP_PASSWORD), [235])) {
        fclose($socket);
        $error = 'SMTP authentication failed. Check the username and app password.';
        return false;
    }

    $fromEmail = clean_mail_header(SMTP_FROM_EMAIL);
    $toEmail = clean_mail_header($toEmail);
    $toName = clean_mail_header($toName);
    $fromName = clean_mail_header(SMTP_FROM_NAME);

    if (!smtp_command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250])
        || !smtp_command($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251])
        || !smtp_command($socket, 'DATA', [354])) {
        fclose($socket);
        $error = 'The email server rejected the message.';
        return false;
    }

    $subject = 'Reset your MyFriend password';
    $safeName = h($toName !== '' ? $toName : 'MyFriend user');
    $safeUrl = h($resetUrl);

    $htmlBody = '<!doctype html><html><body style="font-family:Arial,sans-serif;color:#111">'
        . '<h2>Reset your MyFriend password</h2>'
        . '<p>Hello ' . $safeName . ',</p>'
        . '<p>A request was made to reset your MyFriend password.</p>'
        . '<p><a href="' . $safeUrl . '" style="background:#111;color:#fff;padding:12px 18px;text-decoration:none">Reset Password</a></p>'
        . '<p>This link expires in 30 minutes. Ignore this email if you did not request a reset.</p>'
        . '<p>MyFriend</p></body></html>';

    $boundary = 'myfriend_' . bin2hex(random_bytes(8));
    $headers = [
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'To: ' . ($toName !== '' ? $toName . ' ' : '') . '<' . $toEmail . '>',
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'Message-ID: <' . bin2hex(random_bytes(12)) . '@myfriend.local>',
        'Date: ' . date(DATE_RFC2822),
    ];

    $message = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody;
    $message = preg_replace('/^\./m', '..', $message);
    fwrite($socket, $message . "\r\n.\r\n");

    $response = smtp_read_response($socket);
    $accepted = (int)substr($response, 0, 3) === 250;
    smtp_command($socket, 'QUIT', [221, 250]);
    fclose($socket);

    if (!$accepted) {
        $error = 'The email server did not accept the reset email.';
        return false;
    }

    return true;
}
