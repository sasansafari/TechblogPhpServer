<?php

// PodcastModel
require_once __DIR__ . "/Model/PodcastModel.php";
$PodcastModel = new PodcastModel();
require_once __DIR__ . '/../../assets/public/functions.php';

$command = sanitize($_GET['command']);
switch ($command) {

    case "new":
        $user_id = @$_GET['user_id'] ? sanitize($_GET['user_id']) : null;
        $response = $PodcastModel->getNewPodcasts($user_id);
        break;

    case "get_files":
        $podcats_id = sanitize($_GET['podcats_id']);
        $response = $PodcastModel->getFiles($podcats_id);
        break;

    case "favorites":
        $user_id = sanitize($_GET['user_id']);
        $response = $PodcastModel->favorites($user_id);
        break;

    case "published_by_me":
        $user_id = sanitize($_GET['user_id']);
        $response = $PodcastModel->published_by_me($user_id);
        break;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($response);
