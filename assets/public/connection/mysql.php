<?php ob_start(); ?>
<?php

$host = "localhost";
$charset = "utf8";

// $db_name = "tech_blog";
// $user_db = "root";
// $password_db = "";

$db_name = "geekhaco_techblog";
$user_db = "geekhaco_techblog";
$password_db = "geekhaco_techblog";

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