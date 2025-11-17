<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/UserProfile.php';
require_once __DIR__ . '/../models/UserLog.php';
require_once __DIR__ . '/../middleware/JwtMiddleware.php';

class AuthController {
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

    public function register($payload) {
        // Basic validation
        if (empty($payload->username) || empty($payload->email) || empty($payload->password)) {
            Response::json(['error'=>'username, email and password are required'], 400);
        }
        // Check existing
        if ($this->userModel->findByUsernameOrEmail($payload->username) || $this->userModel->findByUsernameOrEmail($payload->email)) {
            Response::json(['error'=>'Username or email already exists'], 409);
        }

        $password_hash = password_hash($payload->password, PASSWORD_BCRYPT);
        $role = $payload->role ?? 'student';

        $this->userModel->create($payload->username, $payload->email, $password_hash, $role);
        $id = $this->db->lastInsertId();

        // optional create profile
        if (!empty($payload->first_name) || !empty($payload->last_name)) {
            $this->profileModel->createOrUpdate($id, [
                'first_name'=>$payload->first_name ?? null,
                'last_name'=>$payload->last_name ?? null
            ]);
        }

        $this->logModel->add($id, 'REGISTER', ['username'=>$payload->username]);

        Response::json(['message'=>'User registered successfully','user_id'=>$id],201);
    }

    public function login($payload) {
        if (empty($payload->username) || empty($payload->password)) {
            Response::json(['error'=>'username and password required'], 400);
        }
        $user = $this->userModel->findByUsernameOrEmail($payload->username);
        if (!$user || !password_verify($payload->password, $user['password_hash'])) {
            Response::json(['error'=>'Invalid credentials'], 401);
        }
        if ($user['status'] !== 'active') {
            Response::json(['error'=>'Account not active'], 403);
        }
        $token = JwtMiddleware::generateToken([
            'user_id' => intval($user['user_id']),
            'username' => $user['username'],
            'role' => $user['role']
        ], 3600);

        // store last token optionally
        $this->userModel->update($user['user_id'], ['user_token' => $token]);
        $this->logModel->add($user['user_id'], 'LOGIN');

        Response::json(['user_token'=>$token, 'user_id'=>intval($user['user_id']), 'role'=>$user['role']]);
    }
}
