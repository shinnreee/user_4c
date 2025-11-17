<?php
class LoggerMiddleware {
    private $logModel;
    public function __construct($db) {
        $this->logModel = new UserLog($db);
    }
    public function log($user_id, $action, $meta = null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $this->logModel->add($user_id, $action, $meta, $ip);
    }
}
