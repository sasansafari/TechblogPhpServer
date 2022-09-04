<?php

require_once __DIR__ . '/../../../assets/public/connection/mysql.php';
require_once __DIR__ . '/../../../assets/services/convertDate.php';

class ArticleModel
{

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function findAll_By_id($tbl_name, $field_name, $id)
    {
        $query = "select * from " . $tbl_name . " where  " . $field_name . "  =  " . $id . " order by id desc limit 10 ";
        $stm = $this->pdo->prepare($query);
        $stm->execute();
        return  $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAll_categories()
    {
        $stm = $this->pdo->prepare("select * from category order by id desc");
        $stm->execute();
        return  $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAll_tags()
    {
        $stm = $this->pdo->prepare("select * from tags order by id desc");
        $stm->execute();
        return  $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByID($tbl_name, $field_name, $id)
    {
        $stm = $this->pdo->prepare("select * from {$tbl_name}  where {$field_name} = {$id} ");
        $stm->execute();
        return  $stm->fetch(PDO::FETCH_ASSOC);
    }

    public function isFavorite($article_id, $user_id)
    {
        $stm = $this->pdo->prepare("select id from favorite_article where article_id = :article_id and user_id = :user_id");
        $stm->bindParam('article_id', $article_id);
        $stm->bindParam('user_id', $user_id);
        $stm->execute();
        if ($stm->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function findRelated_by_id($tbl_name, $field_name, $id)
    {
        $stm = $this->pdo->prepare("select * from $tbl_name  where $field_name = $id ");
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateViewNumber($article_id)
    {
        $stm = $this->pdo->prepare("update article set view = view + 1 where id = :article_id");
        $stm->bindParam('article_id', $article_id);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    public function getNewArticles($user_id)
    {
        $stm = $this->pdo->prepare("select * from article where status ='1'  order by id desc limit 10 ");
        $stm->execute();
        $result = $stm->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $item) {
            $cat_name = $this->findByID('category', 'id', $item['cat_id']);
            $author = $this->findByID('users', 'id', $item['author_id']);
            // isFavorite
            if (null !== $user_id) {
                $isFavorite = $this->isFavorite($item['id'], $user_id);
            } else {
                $isFavorite = false;
            }
            $response[] = ['id' => $item['id'], 'title' => $item['title'], 'image' => $item['image'], 'cat_id' => $item['cat_id'], 'cat_name' => $cat_name['title'], 'author' => $author['name'], 'view' => $item['view'], 'status' => $item['status'], 'isFavorite' => $isFavorite, 'created_at' => convertDateToJalali_date($item['created_at'])];
        }
        return $response;
    }
    


    public function articleInfo($id, $user_id)
    {

        // article info
        $result = $this->findByID('article', 'id', $id);
        $cat_name = $this->findByID('category', 'id', $result['cat_id']);
        $author = $this->findByID('users', 'id', $result['author_id']);
        // get tags
        $tags = $this->findTags_byID($id);


        // isFavorite
        if (null !== $user_id) {
            $isFavorite = $this->isFavorite($result['id'], $user_id);
        } else {
            $isFavorite = false;
        }

        // related
        $relatedArticles = $this->findRelated_by_id('article', 'cat_id', $result['cat_id']);
        if ($relatedArticles) {
            foreach ($relatedArticles as $item) {
                $cat_name = $this->findByID('category', 'id', $item['cat_id']);
                $author = $this->findByID('users', 'id', $item['author_id']);
                $related[] = ['id' => $item['id'], 'title' => $item['title'], 'image' => $item['image'], 'cat_id' => $item['cat_id'], 'cat_name' => $cat_name['title'], 'author' => $author['name'], 'view' => $item['view'], 'status' => $item['status'], 'created_at' => convertDateToJalali_date($item['created_at'])];
            }
        } else {
            $related = [];
        }

        // updateView
        $this->updateViewNumber($result['id']);

        $response = [
            'info' =>
            array(
                'id' => $result['id'], 'title' => $result['title'], 'content' => $result['content'], 'image' => $result['image'], 'cat_id' => $result['cat_id'], 'cat_name' => $cat_name['title'], 'author' => $author['name'], 'view' => $result['view'], 'status' => $result['status'], 'created_at' => convertDateToJalali_date($result['created_at'])
            ),
            'isFavorite' => $isFavorite,
            'related' => $related,
            'tags' => $tags
        ];
        return $response;
    }


    public function findTags_byID($article_id)
    {
        $stm = $this->pdo->prepare("select * from tags_article  where article_id = :article_id ");
        $stm->bindParam('article_id', $article_id);
        $stm->execute();
        $result = $stm->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $item) {
            $tagInfo = $this->findByID('tags', 'id', $item['tag_id']);
            $response[] = ['id' => $tagInfo['id'], 'title' => $tagInfo['title']];
        }
        return $response;
    }

    public function get_articles_with_cat_id($cat_id, $user_id)
    {

        $result = $this->findAll_By_id('article', 'cat_id', $cat_id);
        foreach ($result as $item) {
            $cat_name = $this->findByID('category', 'id', $item['cat_id']);
            $author = $this->findByID('users', 'id', $item['author_id']);
            // isFavorite
            if (null !== $user_id) {
                $isFavorite = $this->isFavorite($item['id'], $user_id);
            } else {
                $isFavorite = false;
            }
            $response[] = ['id' => $item['id'], 'title' => $item['title'], 'image' => $item['image'], 'cat_id' => $item['cat_id'], 'cat_name' => $cat_name['title'], 'author' => $author['name'], 'view' => $item['view'], 'status' => $item['status'], 'isFavorite' => $isFavorite, 'created_at' => convertDateToJalali_date($item['created_at'])];
        }
        return $response;
    }

    public function get_articles_with_tag_id($tag_id, $user_id)
    {

        $result = $this->findAll_By_id('tags_article', 'tag_id', $tag_id);
        foreach ($result as $item) {

            $articles = $this->findAll_By_id('article', 'id', $item['article_id']);
            foreach ($articles as $article) {
                // isFavorite
                if (null !== $user_id) {
                    $isFavorite = $this->isFavorite($item['id'], $user_id);
                } else {
                    $isFavorite = false;
                }
                $cat_name = $this->findByID('category', 'id', $article['cat_id']);
                $author = $this->findByID('users', 'id', $article['author_id']);
                $response[] = ['id' => $article['id'], 'title' => $article['title'], 'image' => $article['image'], 'cat_id' => $article['cat_id'], 'cat_name' => $cat_name['title'], 'author' => $author['name'], 'view' => $article['view'], 'status' => $article['status'], 'isFavorite' => $isFavorite, 'created_at' => convertDateToJalali_date($article['created_at'])];
            }
        }
        return $response;
    }

    public function store($user_id, $title, $image, $content, $cat_id, $tag_list)
    {

        // upload = image
        $image_url = $this->upload($image);

        // store article
        $stm = $this->pdo->prepare("insert into article (title, image, content, cat_id, author_id) values (:title, :image, :content, :cat_id, :author_id)");
        $stm->bindParam('title', $title);
        $stm->bindParam('image', $image_url);
        $stm->bindParam('content', $content);
        $stm->bindParam('cat_id', $cat_id);
        $stm->bindParam('author_id', $user_id);
        if ($stm->execute()) {
            $article_id = $this->pdo->lastInsertId();
            // store_tags
            $this->store_tags($tag_list, $article_id);
            return true;
        }
        return false;
    }

    public function update($article_id, $user_id, $title, $image, $content, $cat_id, $tag_list)
    {
        $articleInfo = $this->findByID('article', 'id', $article_id);

        // update = image
        if (@$image['name']) {
            $this->removeFile($articleInfo['image']);
            @$image_url = $this->upload($image);
        } else {
            @$image_url = $articleInfo['image'];
        }

        // store article
        $stm = $this->pdo->prepare("update article set title = :title, image = :image, content = :content, cat_id = :cat_id where id = :article_id");
        $stm->bindParam('article_id', $article_id);
        $stm->bindParam('title', $title);
        $stm->bindParam('image', $image_url);
        $stm->bindParam('content', $content);
        $stm->bindParam('cat_id', $cat_id);
        if ($stm->execute()) {
            // remove old tags and store new tags
            $this->removeTags($article_id);
            $this->store_tags($tag_list, $article_id);
            return true;
        }
        return false;
    }

    public function delete_article($user_id, $article_id)
    {
        $articleInfo = $this->findByID('article', 'id', $article_id);
        if ($articleInfo && $articleInfo['author_id'] == $user_id) {
            $response = $this->remove($article_id);
            if ($response) {
                $this->removeTags($article_id);
                $this->removeFile($articleInfo['image']);
                return true;
            }
        }
        return false;
    }

    public function remove($article_id)
    {
        $stm = $this->pdo->prepare("delete from article where id = :article_id");
        $stm->bindParam('article_id', $article_id);
        if ($stm->execute()) {
            return true;
        }
        return false;
    }

    public function removeTags($article_id)
    {
        $stm = $this->pdo->prepare("delete from tags_article where article_id = :article_id");
        $stm->bindParam('article_id', $article_id);
        $stm->execute();
    }

    public function store_favorite($article_id, $user_id)
    {
        $isExist = $this->checkFavoriteArticle($article_id, $user_id);
        if (!$isExist) {
            $stm = $this->pdo->prepare("insert into favorite_article (article_id, user_id) values (:article_id, :user_id)");
            $stm->bindParam('article_id', $article_id);
            $stm->bindParam('user_id', $user_id);
            if ($stm->execute()) {
                return true;
            }
            return false;
        }
    }

    public function favorites($user_id)
    {
        $result = $this->findAll_By_id('favorite_article', 'user_id', $user_id);
        $response = [];

        if (count($result) > 0) {
            foreach ($result as $item) {
                $article = $this->findByID('article', 'id', $item['article_id']);
                $cat_name = $this->findByID('category', 'id', $article['cat_id']);
                $author = $this->findByID('users', 'id', $article['author_id']);

                $response[] = ['fav_id' => $item['id'], 'article_id' => $article['id'],  'title' => $article['title'], 'image' => $article['image'], 'cat_id' => $article['cat_id'], 'cat_name' => $cat_name['title'], 'author' => $author['name'], 'view' => $article['view'], 'status' => $article['status'], 'created_at' => convertDateToJalali_date($article['created_at'])];
            }
        }

        return $response;
    }

    public function published_by_me($user_id)
    {
        $result = $this->findAll_By_id('article', 'author_id', $user_id);
        $response = [];

        if (count($result) > 0) {
            foreach ($result as $article) {
                $cat_name = $this->findByID('category', 'id', $article['cat_id']);
                $author = $this->findByID('users', 'id', $article['author_id']);
                $response[] = ['id' => $article['id'], 'title' => $article['title'], 'image' => $article['image'], 'cat_id' => $article['cat_id'], 'cat_name' => $cat_name['title'], 'author' => $author['name'], 'view' => $article['view'], 'status' => $article['status'], 'created_at' => convertDateToJalali_date($article['created_at'])];
            }
        }
        return $response;
    }

    public function delete_favorite($fav_id)
    {
        $stm = $this->pdo->prepare("delete from favorite_article where id = :fav_id");
        $stm->bindParam('fav_id', $fav_id);
        $stm->execute();
        return true;
    }

    public function checkFavoriteArticle($article_id, $user_id)
    {
        $stm = $this->pdo->prepare("select id from favorite_article where article_id = :article_id and user_id = :user_id ");
        $stm->bindParam('article_id', $article_id);
        $stm->bindParam('user_id', $user_id);
        $stm->execute();
        if ($stm->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function change_status($article_id, $status)
    {
        $stm = $this->pdo->prepare("update article set status = :status where id = :article_id");
        $stm->bindParam('article_id', $article_id);
        $stm->bindParam('status', $status);
        $stm->execute();
        if ($stm->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function removeFile($image_url)
    {
        $file_name = explode('/', $image_url);
        $destination = __DIR__ . '/../../../assets/upload/images/article/' . end($file_name);
        @unlink($destination);
    }

    public function upload($image)
    {
        $new_name = date("YmdHis") . '.' . explode('.', $image['name'])[1];
        $destination = __DIR__ . '/../../../assets/upload/images/article/' . $new_name;
        move_uploaded_file($image['tmp_name'], $destination);
        return "/Techblog/assets/upload/images/article/" . $new_name;
    }

    public function store_tags($tag_list, $article_id)
    {
        foreach (@$tag_list as $item) {
            $stm = $this->pdo->prepare("insert into tags_article (tag_id, article_id) values (:tag_id, :article_id)");
            $stm->bindParam('tag_id', $item);
            $stm->bindParam('article_id', $article_id);
            $stm->execute();
        }
    }
}
