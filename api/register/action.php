<?php

// phpMailer 
require_once __DIR__ . "/../../assets/services/phpMailer/send.php";

// include HomeModel
require_once __DIR__ . "/Model/RegisterModel.php";
$RegisterModel = new RegisterModel();

// sanitize function
require_once __DIR__ . '/../../assets/public/functions.php';
$command = sanitize($_GET['command']);

switch ($command) {
    case "register":
        $email = sanitize($_POST['email']);
        $userInfo = $RegisterModel->store($email);
        $smtpConfig = $RegisterModel->smtpConfig();
        $verifyCode = $RegisterModel->generateVerifyCode();
        $RegisterModel->storeCode($verifyCode, $userInfo['user_id']);

        // send email
        sendMail($smtpConfig['host'], $smtpConfig['username'], $smtpConfig['password'], $smtpConfig['port'], $smtpConfig['set_from'], $smtpConfig['title_from'], $smtpConfig['subject'], $smtpConfig['content'], $verifyCode, $email);

        $response = array('response' => true, 'user_id' => $userInfo['user_id']);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        break;

    case "verify":
        $email = sanitize($_POST['email']);
        $user_id = sanitize($_POST['user_id']);
        $verifyCode = sanitize($_POST['code']);
        $isValid = $RegisterModel->codeValidation($verifyCode, $user_id);

        if ($isValid['success']) {
            @$result = $RegisterModel->diffTime($isValid['data']);
            @$response = array('response' => $result['response'], 'user_id' => $user_id);
        } else {
            $response = $response = array('response' => false, 'user_id' => $user_id);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        break;
}

