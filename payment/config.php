<?php
    require '../vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

    return [
        'merchant_id' => '1235867',
        'merchant_secret' => 'MTY3MzQxNjc5ODMxMTQ3Mzc2NTMxNTk2OTc5MzM1MjQ0NDQ2NjgwMw==',

        'db' => [
            'host' => $_ENV['DB_HOST'],
            'user' => $_ENV['DB_USER'],
            'pass' => $_ENV['DB_PASS'],
            'name' => $_ENV['DB_NAME'],
        ],

        'smtp' => [
            'host' => $_ENV['SMTP_HOST'],
            'username' => $_ENV['SMTP_USERNAME'],
            'password' => $_ENV['SMTP_PASSWORD'],
            'port' => $_ENV['SMTP_PORT'],
            'from_email' => $_ENV['FROM_EMAIL'],
            'from_name' => $_ENV['FROM_NAME'],
        ],
    ];
?>
