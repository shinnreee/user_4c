<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/utils/Response.php';

// autoload (if using composer)
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require __DIR__.'/../vendor/autoload.php';
}

// manual requires if not using composer/autoloader
foreach (glob(__DIR__ . '/../src/**/*.php') as $f) require_once $f;

$db = (new Database())->connect();
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['REQUEST_URI'];
$base = str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_NAME'] ?? ''); // simple normalization

// Remove query string
$path = parse_url($path, PHP_URL_PATH);

// route mapping (very small router)
if ($path === '/auth/register' && $method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'));
    (new AuthController($db))->register($payload);
}

// login
if ($path === '/auth/login' && $method === 'POST') {
    $payload = json_decode(file_get_contents('php://input'));
    (new AuthController($db))->login($payload);
}

// get all users
if ($path === '/users' && $method === 'GET') {
    (new UserController($db))->getAll();
}

// get user by id: /users/{id}
if (preg_match('#^/users/(\d+)$#', $path, $m) && $method === 'GET') {
    (new UserController($db))->getById((int)$m[1]);
}

// update user
if (preg_match('#^/users/(\d+)$#', $path, $m) && $method === 'PUT') {
    $payload = json_decode(file_get_contents('php://input'));
    (new UserController($db))->update((int)$m[1], $payload);
}

// delete user
if (preg_match('#^/users/(\d+)$#', $path, $m) && $method === 'DELETE') {
    (new UserController($db))->delete((int)$m[1]);
}

// INTERNAL endpoints for other services (no auth or with internal api key)
if ($path === '/internal/getBasicUser' && $method === 'GET') {
    $id = $_GET['id'] ?? null;
    if (!$id) Response::json(['error'=>'id missing'],400);
    $user = (new User($db))->findById($id);
    if (!$user) Response::json(['error'=>'not found'],404);
    $out = [
        'user_id'=> (int)$user['user_id'],
        'name'=> trim(($db->query("SELECT first_name,last_name FROM tbl_user_profiles WHERE user_id={$user['user_id']}")->fetchColumn()) ?? ''),
        'username'=> $user['username'],
        'role'=> $user['role'],
        'status'=> $user['status']
    ];
    Response::json($out);
}

// Internal: getUserStatus
if ($path === '/internal/getUserStatus' && $method === 'GET') {
    $id = $_GET['id'] ?? null;
    if (!$id) Response::json(['error'=>'id missing'],400);
    $user = (new User($db))->findById($id);
    if (!$user) Response::json(['error'=>'not found'],404);
    Response::json(['user_id'=> (int)$user['user_id'], 'status'=>$user['status']]);
}

// Internal: getUserRole
if ($path === '/internal/getUserRole' && $method === 'GET') {
    $id = $_GET['id'] ?? null;
    if (!$id) Response::json(['error'=>'id missing'],400);
    $user = (new User($db))->findById($id);
    if (!$user) Response::json(['error'=>'not found'],404);
    Response::json(['user_id'=> (int)$user['user_id'], 'role'=>$user['role']]);
}

// If nothing matched:
Response::json(['error'=>'Not found'], 404);
