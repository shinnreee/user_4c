<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/UserLog.php';

class UserController {
    private $db;
    private $userModel;
    private $profileModel;
    private $logModel;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->userModel = new User($db);
        $this->profileModel = new UserProfile($db);
        $this->logModel = new UserLog($db);
    }

    public function getAll() {
        $list = $this->userModel->getAll();
        Response::json($list);
    }

    public function getById($id) {
        $user = $this->userModel->findById($id);
        if (!$user) Response::json(['error'=>'User not found'],404);
        $profile = $this->profileModel->getByUserId($id);
        $user['profile'] = $profile ?: new StdClass();
        Response::json($user);
    }

    public function update($id, $payload) {
        // allowed fields
        $allowed = ['email','status','role','username','user_token'];
        $fields = [];
        foreach($allowed as $f) if (isset($payload->$f)) $fields[$f] = $payload->$f;
        if (empty($fields) && empty((array)$payload->profile)) {
            Response::json(['error'=>'No updatable fields provided'],400);
        }
        if (!empty($fields)) $this->userModel->update($id,$fields);
        if (!empty((array)$payload->profile)) {
            $this->profileModel->createOrUpdate($id, (array)$payload->profile);
        }
        $this->logModel->add($id,'UPDATE_USER',['fields'=>array_keys($fields)]);
        Response::json(['message'=>'User updated successfully']);
    }

    public function delete($id) {
        $this->userModel->delete($id);
        $this->logModel->add($id,'DELETE_USER');
        Response::json(['message'=>'User deleted']);
    }
}
