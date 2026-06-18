<?php

require_once __DIR__ . '/app.php';

/*
 | PAYMENT_MODE options:
 | demo            = working local test checkout (no real money moves)
 | payfast_sandbox = PayFast sandbox, requires sandbox credentials
 | payfast_live    = PayFast live, requires verified live credentials
 */
define('PAYMENT_MODE', 'demo');

define('PAYFAST_MERCHANT_ID', '');
define('PAYFAST_MERCHANT_KEY', '');
define('PAYFAST_PASSPHRASE', '');

function payment_is_demo(): bool
{
    return PAYMENT_MODE === 'demo';
}

function payfast_process_url(): string
{
    return PAYMENT_MODE === 'payfast_live'
        ? 'https://www.payfast.co.za/eng/process'
        : 'https://sandbox.payfast.co.za/eng/process';
}

function payfast_signature(array $data, string $passphrase = ''): string
{
    $pairs = [];
    foreach ($data as $key => $value) {
        if ($value === '' || $value === null || $key === 'signature') {
            continue;
        }
        $pairs[] = $key . '=' . urlencode(trim((string)$value));
    }

    $query = implode('&', $pairs);
    if ($passphrase !== '') {
        $query .= '&passphrase=' . urlencode(trim($passphrase));
    }

    return md5($query);
}
