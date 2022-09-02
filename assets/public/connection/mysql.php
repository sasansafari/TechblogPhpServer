<?php ob_start(); ?>
<?php

$host = "localhost";
$db_name = "tech_blog";
$user_db = "root";
$password_db = "";

$charset = "utf8";
$dsn = 'mysql:host=' . $host . ';dbname=' . $db_name . ';charset=' . $charset . '';
try {
    $pdo = new PDO($dsn, $user_db, $password_db);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage());
}
?>
<?php
ob_flush();
ob_end_flush();
?>