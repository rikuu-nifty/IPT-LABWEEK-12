<?php

namespace App\Models;

use App\Models\BaseModel;
use \PDO;

class User extends BaseModel
{
    public function save($data) {
        $sql = "INSERT INTO users 
                SET
                    complete_name=:complete_name,
                    email=:email,
                    password=:password";        
        $statement = $this->db->prepare($sql);
        $password_hash = $this->hashPassword($data['password']);
        $statement->execute([
            'complete_name' => $data['complete_name'],
            'email' => $data['email'],
            'password' => $password_hash
        ]);
        
        return $this->db->lastInsertId();
    }

    protected function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getUserID($email) {
        $sql = "SELECT id FROM users WHERE email = :email";
        $statement = $this->db->prepare($sql);
        $statement->execute(['email' => $email]);
    
        return $statement->fetchColumn();
    }

    public function getAllUsers()
    {
        $sql = "SELECT * FROM users";
        $statement = $this->db->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    public function verifyAccess($email, $password)
    {
        $sql = "SELECT password FROM users WHERE email = :email";
        $statement = $this->db->prepare($sql);
        $statement->execute([
            'email' => $email
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if (empty($result)) {
            return false;
        }

        return password_verify($password, $result['password']);
    }

}