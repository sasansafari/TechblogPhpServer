<?php

// article model
require_once __DIR__ . "/Model/ArticleModel.php";
$ArticleModel = new ArticleModel();
require_once __DIR__ . '/../../assets/public/functions.php';

$command = sanitize($_GET['command']);
switch ($command) {

    case "info":
        $id = sanitize($_GET['id']);
        $user_id = @$_GET['user_id'] ? sanitize($_GET['user_id']) : null;
        $response = $ArticleModel->articleInfo($id, $user_id);
        break;

    case "new":
        $user_id = @$_GET['user_id'] ? sanitize($_GET['user_id']) : null;
        $response = $ArticleModel->getNewArticles($user_id);
        break;

    case "categories":
        $response = $ArticleModel->findAll_categories();
        break;
        
    case "tags":
        $response = $ArticleModel->findAll_tags();
        break;

    case "get_articles_with_cat_id":
        $cat_id = sanitize($_GET['cat_id']);
        $user_id = @$_GET['user_id'] ? sanitize($_GET['user_id']) : null;
        $response = $ArticleModel->get_articles_with_cat_id($cat_id, $user_id);
        break;

    case "get_articles_with_tag_id":
        $tag_id = sanitize($_GET['tag_id']);
        $user_id = @$_GET['user_id'] ? sanitize($_GET['user_id']) : null;
        $response = $ArticleModel->get_articles_with_tag_id($tag_id, $user_id);
        break;

    case "favorites":
        $user_id = sanitize($_GET['user_id']);
        $response = $ArticleModel->favorites($user_id);
        break;

    case "published_by_me":
        $user_id = sanitize($_GET['user_id']);
        $response = $ArticleModel->published_by_me($user_id);
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
