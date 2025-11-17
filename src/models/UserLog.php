<?php
class UserLog {
    private $conn;
    private $table = "tbl_user_logs";

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function add($user_id, $action, $meta=null, $ip=null) {
        $sql = "INSERT INTO {$this->table} (user_id, action, meta, ip_address) VALUES (:uid, :action, :meta, :ip)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':uid' => $user_id,
            ':action' => $action,
            ':meta' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
            ':ip' => $ip
        ]);
    }
}