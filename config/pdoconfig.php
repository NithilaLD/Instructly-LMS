<?php
    require '../vendor/autoload.php';

    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
    
    $DB_host = $_ENV['DB_HOST'];
    $DB_user = $_ENV['DB_USER'];
    $DB_pass = $_ENV['DB_PASS'];
    $DB_name = $_ENV['DB_NAME'];

    try
    {
        $DB_con = new PDO("mysql:host={$DB_host};dbname={$DB_name}",$DB_user,$DB_pass);
        $DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e)
    {
        $e->getMessage();
    }
?>
