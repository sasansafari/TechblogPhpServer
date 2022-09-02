<?php

// JWT Service
require_once __DIR__ . "/../../assets/services/JWT/JwtHandler.php";
$jsonWebToken = new JwtHandler();

// PodcastModel
require_once __DIR__ . "/Model/PodcastModel.php";
$PodcastModel = new PodcastModel();
require_once __DIR__ . '/../../assets/public/functions.php';

$command = sanitize($_GET['command']);
if (apache_request_headers()['Authorization']) {
    $token = apache_request_headers()['Authorization'];

    // validation token
    $isValid = $jsonWebToken->validationToken($token);
    if ($isValid) {

        $command = sanitize($_GET['command']);
        switch ($command) {

            case "store_title":
                $user_id = sanitize($_POST['user_id']);
                $title = sanitize($_POST['title']);
                $cat_id = sanitize($_POST['cat_id']);
                $response = $PodcastModel->store_title($user_id, $title, $cat_id);
                break;

            case "update_poster":
                $podcast_id = sanitize($_POST['podcast_id']);
                $user_id = sanitize($_POST['user_id']);
                $poster = $_FILES['image'];
                $response = $PodcastModel->update_poster($podcast_id, $poster, $user_id);
                break;

            case "update_title":
                $podcast_id = sanitize($_POST['podcast_id']);
                $user_id = sanitize($_POST['user_id']);
                $title = sanitize($_POST['title']);
                $cat_id = sanitize($_POST['cat_id']);
                $response = $PodcastModel->update_title($podcast_id, $title, $cat_id);
                break;

            case "delete":
                $podcast_id = sanitize($_POST['podcast_id']);
                $user_id = sanitize($_POST['user_id']);
                $response = $PodcastModel->delete_podcast($podcast_id, $user_id);
                break;

            case "store_file":
                $podcast_id = sanitize($_POST['podcast_id']);
                $title = sanitize($_POST['title']);
                $length = sanitize($_POST['length']);
                $file = $_FILES['file'];
                $response = $PodcastModel->store_file($podcast_id, $title, $length, $file);
                break;

            case "update_file":
                $file_id = sanitize($_POST['file_id']);
                $title = sanitize($_POST['title']);
                $length = sanitize($_POST['length']);
                $file = @$_FILES['file'];
                $response = $PodcastModel->update_file($file_id, $title, $length, $file);
                break;

            case "delete_file":
                $file_id = sanitize($_POST['file_id']);
                $response = $PodcastModel->delete_file($file_id);
                break;

            case "store_favorite":
                $podcast_id = sanitize($_POST['podcast_id']);
                $user_id = sanitize($_POST['user_id']);
                $response = $PodcastModel->store_favorite($podcast_id, $user_id);
                break;

            case "delete_favorite":
                $fav_id = sanitize($_POST['fav_id']);
                $response = $PodcastModel->delete_favorite($fav_id);
                break;

            case "change_status":
                $podcast_id = sanitize($_POST['podcast_id']);
                $status = sanitize($_POST['status']);
                $response = $PodcastModel->change_status($podcast_id, $status);
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
