<?php
declare(strict_types=1);

// Chargement .env (optionnel)
$env = [];
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $parsed = parse_ini_file($envFile);
    if ($parsed !== false) {
        $env = $parsed;
    }
}

return array(
    'app' => array(
        'name'     => 'Kaay Dem !',
        'url'      => isset($env['APP_URL'])    ? $env['APP_URL']    : 'http://localhost/kaaydem/public',
        'env'      => isset($env['APP_ENV'])    ? $env['APP_ENV']    : 'development',
        'debug'    => isset($env['APP_DEBUG'])  ? filter_var($env['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN) : true,
        'timezone' => 'Africa/Dakar',
        'locale'   => 'fr',
        'secret'   => isset($env['APP_SECRET']) ? $env['APP_SECRET'] : 'kaaydem_secret_key_change_in_production',
    ),
    'db' => array(
        'host'    => isset($env['DB_HOST']) ? $env['DB_HOST'] : '127.0.0.1',
        'port'    => isset($env['DB_PORT']) ? (int)$env['DB_PORT'] : 3306,
        'name'    => isset($env['DB_NAME']) ? $env['DB_NAME'] : 'kaaydem_db',
        'user'    => isset($env['DB_USER']) ? $env['DB_USER'] : 'root',
        'pass'    => isset($env['DB_PASS']) ? $env['DB_PASS'] : '',
        'charset' => 'utf8mb4',
    ),
    'mail' => array(
        'host'     => 'smtp.gmail.com',
        'port'     => 587,
        'username' => isset($env['MAIL_USERNAME']) ? $env['MAIL_USERNAME'] : '',
        'password' => isset($env['MAIL_PASSWORD']) ? $env['MAIL_PASSWORD'] : '',
        'from'     => isset($env['MAIL_FROM'])     ? $env['MAIL_FROM']     : '',
        'name'     => 'Kaay Dem !',
    ),
    'session' => array(
        'name'     => 'kaaydem_session',
        'lifetime' => 7200,
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ),
    'upload' => array(
        'max_size' => 5242880,
        'allowed'  => array('image/jpeg', 'image/png', 'image/webp'),
        'path'     => __DIR__ . '/../public/uploads/',
    ),
    'pagination' => array(
        'per_page' => 10,
    ),
    'price' => array(
        'per_km' => 15.0,
    ),
    'co2' => array(
        'car_per_km' => 120.0,
    ),
    'brute_force' => array(
        'max_attempts' => 5,
        'lockout_time' => 900,
    ),
);