<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . "/JWT.php";
require_once __DIR__ . "/Key.php";
require_once __DIR__ . "/../../../assets/public/connection/mysql.php";

class JwtHandler
{

    private $pdo;
    private $secret_key = '0a9e8496d21b1f519ee0dd6b8e87293a51db5d54c167195257ddfd595df168ab8da2c35325a0847d0ad2bb6ff86a682b8553e16750def76359071ec8';
    private $salt = 'Bearer ';
    private $alg = 'HS256';

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }
    
    public function validationToken($token)
    {
        $stm = $this->pdo->prepare('select id from users where token = :token limit 1');
        $stm->bindParam('token', $token);
        $stm->execute();
        if ($stm->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function sign($payload)
    {
        return $this->salt . JWT::encode($payload, $this->secret_key, $this->alg);
    }

    public function deCoded($token)
    {
        return JWT::decode($token, new Key($this->secret_key, $this->alg));
    }

}