<?php

require_once __DIR__ . '/../../../assets/public/connection/mysql.php';
require_once __DIR__ . '/../../../assets/services/convertDate.php';

class UserModel
{

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function findByID($tbl_name, $id)
    {
        $stm = $this->pdo->prepare("select * from {$tbl_name}  where id = :id ");
        $stm->bindParam('id', $id);
        $stm->execute();
        return  $stm->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($user_id, $name, $image)
    {
        $userInfo = $this->findByID('users', 'id', $user_id);

        // update = image
        if (@$image['name']) {
            $this->removeFile($userInfo['image']);
            @$image_url = $this->upload($image);
        } else {
            @$image_url = $userInfo['image'];
        }

        $stm = $this->pdo->prepare("update users set name = :name, image = :image");
        $stm->bindParam('name', $name);
        $stm->bindParam('image', $image_url);
        if ($stm->execute()) {
            return 'updated';
        }
        return 'error';
    }
    
    public function removeFile($image_url)
    {
        $file_name = explode('/', $image_url);
        $destination = __DIR__ . '/../../../assets/upload/images/users/' . end($file_name);
        unlink($destination);
    }

    public function upload($image)
    {
        $new_name = date("YmdHis") . '.' . explode('.', $image['name'])[1];
        $destination = __DIR__ . '/../../../assets/upload/images/users/' . $new_name;
        move_uploaded_file($image['tmp_name'], $destination);
        return "/Techblog/assets/upload/images/users/" . $new_name;
    }
}
