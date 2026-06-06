<?php
    require '../vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

    $host   = $_ENV['DB_HOST'];
    $db     = $_ENV['DB_NAME'];
    $dbuser = $_ENV['DB_USER'];
    $dbpass = $_ENV['DB_PASS'];
    $mysqli=new mysqli($host,$dbuser, $dbpass, $db);
?>
