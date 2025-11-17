<?php
class UserProfile {
    private $conn;
    private $table = "tbl_user_profiles";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function getByUserId($user_id) {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :uid LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':uid'=>$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createOrUpdate($user_id, $data) {
        $exists = $this->getByUserId($user_id);
        if ($exists) {
            $sets = [];
            $params = [':uid'=>$user_id];
            foreach($data as $k=>$v) {
                $sets[] = "`$k` = :$k";
                $params[":$k"] = $v;
            }
            $sql = "UPDATE {$this->table} SET ".implode(',', $sets)." WHERE user_id = :uid";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } else {
            $cols = ['user_id'];
            $place = [':user_id'];
            $params = [':user_id'=>$user_id];
            foreach($data as $k=>$v) {
                $cols[] = "`$k`";
                $place[] = ":$k";
                $params[":$k"] = $v;
            }
            $sql = "INSERT INTO {$this->table} (".implode(',', $cols).") VALUES (".implode(',', $place).")";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        }
    }
}
