<?php
class Database {
    private $host = "127.0.0.1";
    private $db_name = "user_service";
    private $username = "root";
    private $password = "";
    public $conn;

    public function connect(){
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Emulate prepares off for better security
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "DB Connection failed: " . $e->getMessage()]);
            exit;
        }
        return $this->conn;
    }
}
