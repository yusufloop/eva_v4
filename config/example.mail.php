<?php
// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'username');
define('SMTP_PASSWORD', 'password');
define('SMTP_PORT', 465);
define('SMTP_FROM_EMAIL', 'email');
define('SMTP_FROM_NAME', 'EVA Support - No Reply');

// Email template path
define('EMAIL_TEMPLATE_PATH', '../email_template/');

// Base URL for email links
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/eva');
?>
 