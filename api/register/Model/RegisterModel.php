<?php

require_once __DIR__ . '/../../../assets/public/connection/mysql.php';
require_once __DIR__ . '/../../../assets/services/convertDate.php';

require_once __DIR__ . "/../../../assets/services/JWT/JwtHandler.php";

class RegisterModel
{

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function smtpConfig()
    {
        $stm = $this->pdo->prepare("select * from smtp_config where status = '1' order by id desc limit 1 ");
        $stm->execute();
        return  $stm->fetch(PDO::FETCH_ASSOC);
    }

    public function store($email)
    {
        $response = $this->emailValidation($email);
        if (!$response['success']) {
            return array('success' => true, 'user_id' => $this->insertNewEmail($email));
        }
        return array('success' => true, 'user_id' => $response['data']['id']);
    }

    public function updateToken($user_id, $token)
    {
        $stm = $this->pdo->prepare("update users set token = :token where id = :id");
        $stm->bindParam('token', $token);
        $stm->bindParam('id', $user_id);
        $stm->execute();
    }

    public function insertNewEmail($email)
    {
        $stm = $this->pdo->prepare("insert into users (email) values (:email)");
        $stm->bindParam('email', $email);
        $stm->execute();
        return $this->pdo->lastInsertId();
    }

    public function removeVeryCode($user_id)
    {
        $stm = $this->pdo->prepare("delete from verify_code where user_id = :user_id");
        $stm->bindParam('user_id', $user_id);
        $stm->execute();
    }

    public function storeCode($code, $user_id)
    {
        date_default_timezone_set("Asia/Tehran");
        $created_at = date("Y-m-d H:i:s");
        $this->removeVeryCode($user_id);

        $stm = $this->pdo->prepare("insert into verify_code (user_id, code, created_at) values (:user_id, :code, :created_at)");
        $stm->bindParam('code', $code);
        $stm->bindParam('user_id', $user_id);
        $stm->bindParam('created_at', $created_at);
        $stm->execute();
    }

    public function codeValidation($code, $user_id)
    {
        $stm = $this->pdo->prepare("select * from verify_code where user_id = :user_id and code = :code order by id desc limit 1 ");
        $stm->bindParam('code', $code);
        $stm->bindParam('user_id', $user_id);
        $stm->execute();
        if ($stm->rowCount() > 0) {
            return array('success' => true, 'data' => $stm->fetch(PDO::FETCH_ASSOC));
        }
        return array('success' => false, 'data' => null);
    }

    public function generateVerifyCode()
    {
        return rand(111111, 999999);
    }


    public function findById($tbl_name, $field_name, $value)
    {
        $query = "select * from  " . $tbl_name . "  where " . $field_name . " =  " . $value . " order by id desc limit 1 ";
        $stm = $this->pdo->prepare($query);
        $stm->execute();
        if ($stm->rowCount() > 0) {
            return array('success' => true, 'data' => $stm->fetch(PDO::FETCH_ASSOC));
        }
        return array('success' => false, 'data' => null);
    }

    public function diffTime($data)
    {

        date_default_timezone_set("Asia/Tehran");
        // get TimeZone For Asia/Tehran
        $timeZone = new DateTimeZone("Asia/Tehran");
        $currentDate = new DateTime();
        $currentDate->setTimezone($timeZone);

        if (($currentDate->getTimestamp() - strtotime($data['created_at'])) > (3 * 60)) {
            return array("response" => 'expired');
        } else {
            $userInfo = $this->findById('users', 'id', $data['user_id']);

            $jsonWebToken = new JwtHandler();

            $payload = array('id' => $userInfo['data']['id'], 'email' => $userInfo['data']['email']);
            $token = $jsonWebToken->sign($payload);
            $this->updateToken($userInfo['data']['id'], $token);
            return array("response" => 'verified');
        }
    }

    public function emailValidation($email)
    {
        $stm = $this->pdo->prepare("select * from users where email = :email order by id desc limit 1 ");
        $stm->bindParam('email', $email);
        $stm->execute();
        if ($stm->rowCount() > 0) {
            return array('success' => true, 'data' => $stm->fetch(PDO::FETCH_ASSOC));
        }
        return array('success' => false, 'data' => null);
    }
}
