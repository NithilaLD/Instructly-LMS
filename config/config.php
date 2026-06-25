<?php
    date_default_timezone_set('Asia/Colombo');
    require '../vendor/autoload.php';
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

    $dbhost = $_ENV['DB_HOST'];
    $dbname = $_ENV['DB_NAME'];
    $dbuser = $_ENV['DB_USER'];
    $dbpass = $_ENV['DB_PASS'];
    $mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

    return [
        'payhere' => [
            'merchant_id' => $_ENV['Merchant_ID'],
            'merchant_secret' => $_ENV['Merchant_Secret'],
            'return_url' => $_ENV['Return_URL'],
            'cancel_url' => $_ENV['Cancel_URL'],
            'notify_url' => $_ENV['Notify_URL'],
        ],
        'smtp' => [
            'host' => $_ENV['SMTP_HOST'],
            'username' => $_ENV['SMTP_USERNAME'],
            'password' => $_ENV['SMTP_PASSWORD'],
            'port' => $_ENV['SMTP_PORT'],
            'from_email' => $_ENV['FROM_EMAIL'],
            'from_name' => $_ENV['FROM_NAME'],
            'api_key' => $_ENV['GEMINI_API_KEY'],
        ],
    ];
?>