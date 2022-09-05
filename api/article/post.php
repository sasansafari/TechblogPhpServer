<?php


// JWT Service
require_once __DIR__ . "/../../assets/services/JWT/JwtHandler.php";
$jsonWebToken = new JwtHandler();

// article model
require_once __DIR__ . "/Model/ArticleModel.php";
$ArticleModel = new ArticleModel();
require_once __DIR__ . '/../../assets/public/functions.php';

if (apache_request_headers()['Authorization']) {
    $token = apache_request_headers()['Authorization'];

    // validation token
    $isValid = $jsonWebToken->validationToken($token);
    if ($isValid) {
        $command = sanitize($_POST['command']);
        switch ($command) {

            case "store":
                $user_id = sanitize($_POST['user_id']);
                $title = sanitize($_POST['title']);
                $image = $_FILES['image'];
                $content = sanitize($_POST['content']);
                $cat_id = sanitize($_POST['cat_id']);
                $tag_list = sanitize($_POST['tag_list']);

                $response = $ArticleModel->store($user_id, $title, $image, $content, $cat_id, json_decode($tag_list, true));
                break;

            case "delete_article":
                $user_id = sanitize($_POST['user_id']);
                $article_id = sanitize($_POST['article_id']);
                $response = $ArticleModel->delete_article($user_id, $article_id);
                break;


            case "update":
                $article_id = sanitize($_POST['article_id']);
                $user_id = sanitize($_POST['user_id']);
                $title = sanitize($_POST['title']);
                @$image = @$_FILES['image'];
                $content = sanitize($_POST['content']);
                $cat_id = sanitize($_POST['cat_id']);
                $tag_list = sanitize($_POST['tag_list']);

                $response = $ArticleModel->update($article_id, $user_id, $title, $image, $content, $cat_id, json_decode($tag_list, true));
                break;

            case "store_favorite":
                $article_id = sanitize($_POST['article_id']);
                $user_id = sanitize($_POST['user_id']);
                $response = $ArticleModel->store_favorite($article_id, $user_id);
                break;

            case "delete_favorite":
                $fav_id = sanitize($_POST['fav_id']);
                $user_id = sanitize($_POST['user_id']);
                $response = $ArticleModel->delete_favorite($fav_id);
                break;

            case "change_status":
                $article_id = sanitize($_POST['article_id']);
                $status = sanitize($_POST['status']);
                $response = $ArticleModel->change_status($article_id, $status);
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
