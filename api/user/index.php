<?php

require_once __DIR__ . "/../../assets/services/JWT/JwtHandler.php";
$jsonWebToken = new JwtHandler();

// include HomeModel
require_once __DIR__ . "/Model/UserModel.php";
$UserModel = new UserModel();
require_once __DIR__ . '/../../assets/public/functions.php';

if (apache_request_headers()['Authorization']) {
    $token = apache_request_headers()['Authorization'];

    // validation token
    $isValid = $jsonWebToken->validationToken($token);
    if ($isValid) {

        $command = sanitize($_GET['command']);
        switch ($command) {
            case "info":
                $user_id = sanitize($_GET['user_id']);
                $response = $UserModel->findByID('users', $user_id);
                break;

            case "update":
                $user_id = sanitize($_POST['user_id']);
                $name = sanitize($_POST['name']);
                $image = $_FILES['image'];
                $response = $UserModel->update($user_id, $name, $image);
                break;
        }

        $response = array('status_code' => 200, 'response' => $response, 'msg' => 'Your Token is valid!');
    } else {
        $response = array('status_code' => 403, 'response' => [], 'msg' => 'Your Token is not valid!');
    }
} else {
    $response = array('status_code' => 401, 'response' => [], 'msg' => 'You are not Authorized!');
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
