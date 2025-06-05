<?php
require_once __DIR__ . '/../classes/Auth.php';

$data = json_decode(file_get_contents(DB_FILE), true);
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

if ($uri === '/account' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $input['id'] = time();
    $data['users'][] = $input;
    file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
    echo json_encode(["message" => "Account created"]);
    exit;
}

if ($uri === '/token' && $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    foreach ($data['users'] as $user) {
        if ($user['email'] === $input['email'] && $user['password'] === $input['password']) {
            $jwt = Auth::generateJWT($user['email']);
            echo json_encode(["token" => $jwt]);
            exit;
        }
    }
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    exit;
}
