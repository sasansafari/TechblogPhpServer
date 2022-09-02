<?php

require_once __DIR__ . '/../../../assets/public/connection/mysql.php';
require_once __DIR__ . '/../../../assets/services/convertDate.php';

class HomeModel
{

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function findAllArticle($order)
    {
        $stm = $this->pdo->prepare("select id, title, image, cat_id, author_id, view, status, created_at from article where status = '1'  {$order}");
        $stm->execute();
        return  $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAllTags()
    {
        $stm = $this->pdo->prepare("select * from tags order by id desc");
        $stm->execute();
        return  $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAllCategories()
    {
        $stm = $this->pdo->prepare("select * from category order by id desc");
        $stm->execute();
        return  $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByID($tbl_name, $id)
    {
        $stm = $this->pdo->prepare("select * from {$tbl_name}  where id = :id ");
        $stm->bindParam('id', $id);
        $stm->execute();
        return  $stm->fetch(PDO::FETCH_ASSOC);
    } 

    public function findPoster()
    {
        $stm = $this->pdo->prepare("select id, title, image from article  where status = '1' order by id desc limit 1");
        $stm->execute();
        return  $stm->fetch(PDO::FETCH_ASSOC);
    }  
    
    public function findAllPodcats()
    {
        $stm = $this->pdo->prepare("select * from podcast order by id desc limit 10 ");
        $stm->execute();
        return  $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function homeItems()
    {
 
        $top_visited = $this->findAllArticle('order by view desc limit 10 ');
        foreach ($top_visited as $item) {
            $cat_name = $this->findByID('category', $item['cat_id']);
            $author = $this->findByID('users', $item['author_id']);
            $topVisited[] = ['id' => $item['id'], 'title' => $item['title'], 'image' => $item['image'], 'cat_id' => $item['cat_id'], 'cat_name' => $cat_name['title'], 'author' => $author['name'], 'view' => $item['view'], 'status' => $item['status'],  'created_at' => convertDateToJalali_date($item['created_at'])];
        }

        $poster = $this->findPoster();
        $tags = $this->findAllTags();
        $categories = $this->findAllCategories();
        $top_podcasts = $this->findAllPodcats();
        foreach ($top_podcasts as $item) {
            $publisher = $this->findByID('users', $item['user_id']);
            $topPodcasts[] = ['id' => $item['id'], 'title' => $item['title'], 'poster' => $item['poster'], 'publisher' => $publisher['name'],  'view' => $item['view'], 'created_at' => convertDateToJalali_date($item['created_at'])];
        }

        return array('poster' => $poster, 'top_visited' => $topVisited, 'top_podcasts' => $topPodcasts, 'tags' => $tags, 'categories' => $categories);
    }
}
