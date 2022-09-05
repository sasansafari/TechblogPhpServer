<?php

require_once __DIR__ . '/../../../assets/public/connection/mysql.php';
require_once __DIR__ . '/../../../assets/services/convertDate.php';

class PodcastModel
{

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function findAll_By_id($tbl_name, $field_name, $id)
    {
        $stm = $this->pdo->prepare("select * from {$tbl_name}  where {$field_name} = {$id} order by id desc");
        $stm->execute();
        return  $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByID($tbl_name, $field_name, $id)
    {
        $stm = $this->pdo->prepare("select * from {$tbl_name}  where {$field_name} = {$id} ");
        $stm->execute();
        return  $stm->fetch(PDO::FETCH_ASSOC);
    }

    public function isFavorite($podcast_id, $user_id)
    {
        $stm = $this->pdo->prepare("select id from favorite_podcast where podcast_id = :podcast_id and user_id = :user_id");
        $stm->bindParam('podcast_id', $podcast_id);
        $stm->bindParam('user_id', $user_id);
        $stm->execute();
        if ($stm->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getNewPodcasts($user_id)
    {
        $stm = $this->pdo->prepare("select * from podcast order by id desc limit 10 ");
        $stm->execute();
        $result = $stm->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $item) {

            // isFavorite
            if (null !== $user_id) {
                $isFavorite = $this->isFavorite($item['id'], $user_id);
            } else {
                $isFavorite = false;
            }

            $publisher = $this->findByID('users', 'id', $item['user_id']);
            $response[] = ['id' => $item['id'], 'title' => $item['title'], 'poster' => $item['poster'],   'publisher' => $publisher['name'], 'view' => $item['view'], 'status' => $item['status'], 'created_at' => convertDateToJalali_date($item['created_at']), 'isFavorite' => $isFavorite];
        }
        return $response;
    }

    public function related($podcast_id)
    {
        $stm = $this->pdo->prepare("select cat_id from podcast  where id = :podcast_id ");
        $stm->bindParam('podcast_id', $podcast_id);
        $stm->execute();
        $result = $stm->fetch(PDO::FETCH_ASSOC);
        return $this->findAll_By_id('podcast', 'cat_id', $result['cat_id']);
    }

    public function updateView($podcast_id)
    {
        $stm = $this->pdo->prepare("update podcast set view = view + 1 where id = :podcast_id");
        $stm->bindParam('podcast_id', $podcast_id);
        $stm->execute();
    }

    public function files($podcast_id)
    {
        // get files
        $stm = $this->pdo->prepare("select * from podcast_files  where podcast_id = :podcast_id order by id desc");
        $stm->bindParam('podcast_id', $podcast_id);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFiles($podcast_id)
    {
        // update view
        $this->updateView($podcast_id);

        // get files
        $result = $this->files($podcast_id);
        if ($result) {
            foreach ($result as $item) {
                // get related
                                $relatedResult = $this->related($item['podcast_id']);
                if (count($relatedResult) > 0) {
                    $publisher = '';
                    foreach ($relatedResult as $related_item) {
                        $publisher = $this->findByID('users', 'id', $related_item['user_id']);
                        $related[] = ['id' => $related_item['id'], 'title' => $related_item['title'], 'poster' => $related_item['poster'], 'publisher' => $publisher['name'], 'view' => $related_item['view'], 'status' => $related_item['status'],  'created_at' => convertDateToJalali_date($related_item['created_at'])];
                    }
                } else {
                    $related = [];
                }

                $response[] = [
                    'id' => $item['id'], 'podcast_id' => $item['podcast_id'], 'file' => $item['file'], 'title' => $item['title'], 'length' => $item['length']
                ];
            }
        } else {
            $response = [];
            $related = [];

        }


        $finalResult = ['files' => $response, 'related' => $related];
        return $finalResult;
    }

    public function store_title($user_id, $title, $cat_id)
    {
        // store podcats title
        $stm = $this->pdo->prepare("insert into podcast (title, cat_id, user_id) values (:title, :cat_id, :user_id)");
        $stm->bindParam('title', $title, PDO::PARAM_STR);
        $stm->bindParam('cat_id', $cat_id, PDO::PARAM_INT);
        $stm->bindParam('user_id', $user_id, PDO::PARAM_INT);
        try {
            $stm->execute();
            return array('success' => true, 'podcast_id' => $this->pdo->lastInsertId());
        } catch (Exception  $e) {
            return array('success' => false, 'podcast_id' => null);
        }
    }

    public function store_file($podcast_id, $title, $length, $file)
    {
        // upload = file
        $file_url = $this->upload($file, 'files');
        // store podcats title
        $stm = $this->pdo->prepare("insert into podcast_files (podcast_id, file, title, length) values (:podcast_id, :file_url, :title, :length)");
        $stm->bindParam('podcast_id', $podcast_id);
        $stm->bindParam('file_url', $file_url);
        $stm->bindParam('title', $title);
        $stm->bindParam('length', $length);
        if ($stm->execute()) {
            return true;
        }
        return false;
    }

    public function update_poster($podcast_id, $poster, $user_id)
    {

        $posterInfo = $this->findByID('podcast', 'id', $podcast_id);
        if ($poster['name']) {
            // upload = image
            $poster_url = $this->upload($poster, 'images');
            // removeFile
            $this->removeFile($posterInfo['poster'], 'images');
        } else {
            $poster_url = $posterInfo[''];
        }

        // store podcats poster
        $stm = $this->pdo->prepare("update podcast set poster = :poster where id = :podcast_id and user_id = :user_id ");
        $stm->bindParam('podcast_id', $podcast_id);
        $stm->bindParam('poster', $poster_url);
        $stm->bindParam('user_id', $user_id);
        $stm->execute();
        return true;
    }

    public function update_file($file_id, $title, $length, $file)
    {
        $fileInfo = $this->findByID('podcast_files', 'id', $file_id);
        if (@$file['name']) {
            // upload = file
            $file_url = $this->upload($file, 'files');
        } else {
            $file_url = $fileInfo['file'];
        }

        $stm = $this->pdo->prepare("update podcast_files set file = :file_url, title = :title, length = :length where id = :file_id  ");
        $stm->bindParam('file_id', $file_id);
        $stm->bindParam('file_url', $file_url);
        $stm->bindParam('title', $title);
        $stm->bindParam('length', $length);
        if ($stm->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function update_title($podcast_id, $title, $cat_id)
    {

        // update podcats
        $stm = $this->pdo->prepare("update podcast set title = :title, cat_id = :cat_id where id = :podcast_id");
        $stm->bindParam('podcast_id', $podcast_id);
        $stm->bindParam('title', $title);
        $stm->bindParam('cat_id', $cat_id);
        if ($stm->execute()) {
            return true;
        }
        return false;
    }

    public function delete_podcast($podcast_id, $user_id)
    {
        $podcastInfo = $this->findByID('podcast', 'id', $podcast_id);
        if ($podcastInfo['user_id'] == $user_id) {
            $this->remove('podcast', 'id', $podcast_id);
            $this->remove('favorite_podcast', 'podcast_id', $podcast_id);
            $this->removeFile($podcastInfo['poster'], 'images');
            $files = $this->files($podcast_id);
            foreach ($files as $item) {
                $this->remove('podcast_files', 'podcast_id', $podcast_id);
                $this->removeFile($item['file'], 'files');
            }
            return true;
        }
        return false;
    }

    public function delete_file($file_id)
    {
        $filesInfo = $this->findByID('podcast_files', 'id', $file_id);
        $this->removeFile($filesInfo['file'], 'files');
        $this->remove('podcast_files', 'id', $file_id);
        return true;
        return false;
    }

    public function remove($tbl_name, $field, $value)
    {
        $query = "delete from " . $tbl_name . " where  " . $field . "  =  " . $value . " ";
        $stm = $this->pdo->prepare($query);
        $stm->execute();
    }

    public function store_favorite($podcast_id, $user_id)
    {
        $isExist = $this->checkFavoritepodcast($podcast_id, $user_id);
        if (!$isExist) {
            $stm = $this->pdo->prepare("insert into favorite_podcast (podcast_id, user_id) values (:podcast_id, :user_id)");
            $stm->bindParam('podcast_id', $podcast_id);
            $stm->bindParam('user_id', $user_id);
            $stm->execute();
        }
        return true;
    }

    public function favorites($user_id)
    {
        $result = $this->findAll_By_id('favorite_podcast', 'user_id', $user_id);
        if (count($result) > 0 ){
            foreach ($result as $item) {
                $podcast = $this->findByID('podcast', 'id', $item['podcast_id']);
                $cat_name = $this->findByID('category', 'id', $podcast['cat_id']);
                $author = $this->findByID('users', 'id', $podcast['user_id']);
                $response[] = ['fav_id' => $item['id'], 'podcast_id' => $podcast['id'],  'title' => $podcast['title'], 'poster' => $podcast['poster'], 'cat_id' => $podcast['cat_id'], 'cat_name' => $cat_name['title'], 'author' => $author['name'], 'view' => $podcast['view'], 'status' => $podcast['status'], 'created_at' => convertDateToJalali_date($podcast['created_at'])];
            }
        return $response;
        }
        return [];

    }

    public function published_by_me($user_id)
    {
        $result = $this->findAll_By_id('podcast', 'user_id', $user_id);
        if (count($result) > 0 ){
            
        foreach ($result as $podcast) {
            $cat_name = $this->findByID('category', 'id', $podcast['cat_id']);
            $author = $this->findByID('users', 'id', $podcast['user_id']);
            $response[] = ['id' => $podcast['id'], 'title' => $podcast['title'], 'poster' => $podcast['poster'], 'cat_id' => $podcast['cat_id'], 'cat_name' => $cat_name['title'], 'author' => $author['name'], 'view' => $podcast['view'], 'status' => $podcast['status'], 'created_at' => convertDateToJalali_date($podcast['created_at'])];
        }
        return $response;
        }
        return [];
    }

    public function delete_favorite($fav_id)
    {
        $stm = $this->pdo->prepare("delete from favorite_podcast where id = :fav_id");
        $stm->bindParam('fav_id', $fav_id);
        $stm->execute();
        return true;
    }

    public function checkFavoritePodcast($podcast_id, $user_id)
    {
        $stm = $this->pdo->prepare("select id from favorite_podcast where podcast_id = :podcast_id and user_id = :user_id ");
        $stm->bindParam('podcast_id', $podcast_id);
        $stm->bindParam('user_id', $user_id);
        $stm->execute();
        if ($stm->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function change_status($podcast_id, $status)
    {
        $stm = $this->pdo->prepare("update podcast set status = :status where id = :podcast_id");
        $stm->bindParam('podcast_id', $podcast_id);
        $stm->bindParam('status', $status);
        $stm->execute();
        if ($stm->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function removeFile($file_url, $dir_name)
    {
        $file_name = explode('/', $file_url);
        $destination = __DIR__ . '/../../../assets/upload/images/podcast/' . $dir_name . "/"  . end($file_name);
        unlink($destination);
    }

    public function upload($image, $dir_name)
    {
        $new_name = date("YmdHis") . '.' . explode('.', $image['name'])[1];
        $destination = __DIR__ . '/../../../assets/upload/images/podcast/' . $dir_name . "/" . $new_name;
        move_uploaded_file($image['tmp_name'], $destination);
        return "/Techblog/assets/upload/images/podcast/" . $dir_name . "/"  . $new_name;
    }
}
