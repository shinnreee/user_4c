<?php
class User {
    private $conn;
    private $table = "tbl_user_credentials";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function create($username, $email, $password_hash, $role='student') {
        $sql = "INSERT INTO {$this->table} (username, email, password_hash, role) 
                VALUES (:username, :email, :password_hash, :role)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':username'=>$username,
            ':email'=>$email,
            ':password_hash'=>$password_hash,
            ':role'=>$role
        ]);
    }

    public function findByUsernameOrEmail($identifier) {
        $sql = "SELECT * FROM {$this->table} WHERE username = :id OR email = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id'=>$identifier]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $sql = "SELECT user_id, username, email, role, status, created_at FROM {$this->table}";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $fields) {
        $sets = [];
        $params = [':id'=>$id];
        foreach($fields as $k=>$v) {
            $sets[] = "`$k` = :$k";
            $params[":$k"] = $v;
        }
        $sql = "UPDATE {$this->table} SET " . implode(',', $sets) . " WHERE user_id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id'=>$id]);
    }
}
