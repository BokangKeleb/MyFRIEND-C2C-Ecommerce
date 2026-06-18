<?php

define('PAYFAST_SANDBOX', true);

define('PAYFAST_MERCHANT_ID', 'YOUR_PAYFAST_MERCHANT_ID');
define('PAYFAST_MERCHANT_KEY', 'YOUR_PAYFAST_MERCHANT_KEY');
define('PAYFAST_PASSPHRASE', 'YOUR_PAYFAST_PASSPHRASE');

define('PAYFAST_PROCESS_URL', PAYFAST_SANDBOX
    ? 'https://sandbox.payfast.co.za/eng/process'
    : 'https://www.payfast.co.za/eng/process'
);

define('SITE_URL', 'https://yourdomain.com');
