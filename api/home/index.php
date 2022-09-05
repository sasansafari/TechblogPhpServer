<?php
header('Content-Type: application/json; charset=utf-8');

// include HomeModel
require_once __DIR__ . "/Model/HomeModel.php";
$HomeModel = new HomeModel();
require_once __DIR__ . '/../../assets/public/functions.php';

$command = sanitize($_GET['command']);
switch ($command) {
    case "index":
        $response = $HomeModel->homeItems();
        break;
}

echo json_encode($response);
