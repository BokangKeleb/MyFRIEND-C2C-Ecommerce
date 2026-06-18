<?php

require_once __DIR__ . '/app.php';

/*
 | MAIL_MODE options:
 | preview = local testing. A secure reset link is shown on screen instead of emailed.
 | smtp    = sends the reset link through an SMTP account.
 */
define('MAIL_MODE', 'preview');

// SMTP settings. Complete these only when MAIL_MODE is changed to smtp.
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 465);
define('SMTP_ENCRYPTION', 'ssl'); // ssl or tls
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', ''); // Use an app password, not your normal email password.
define('SMTP_FROM_EMAIL', '');
define('SMTP_FROM_NAME', 'MyFriend');
