<?php
require_once __DIR__ . '/../classes/Auth.php';

$data = json_decode(file_get_contents(DB_FILE), true);
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$headers = getallheaders();
$token = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $token);
$user_email = Auth::verifyJWT($token);

if (!$user_email) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// Panier
if (preg_match('#^/cart$#', $uri)) {
    if ($method === 'GET') {
        echo json_encode($data['carts'][$user_email] ?? []);
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $data['carts'][$user_email][] = $input;
        file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
        echo json_encode(["message" => "Product added to cart"]);
    } elseif ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $data['carts'][$user_email] = array_filter(
            $data['carts'][$user_email] ?? [],
            function($p) use ($input) {
                return $p['id'] !== $input['id'];
            }
        );
        file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
        echo json_encode(["message" => "Product removed from cart"]);
    }
}

// Wishlist
if (preg_match('#^/wishlist$#', $uri)) {
    if ($method === 'GET') {
        echo json_encode($data['wishlists'][$user_email] ?? []);
    } elseif ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $data['wishlists'][$user_email][] = $input;
        file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
        echo json_encode(["message" => "Product added to wishlist"]);
    } elseif ($method === 'DELETE') {
        $input = json_decode(file_get_contents('php://input'), true);
        $data['wishlists'][$user_email] = array_filter(
            $data['wishlists'][$user_email] ?? [],
            function($p) use ($input) {
                return $p['id'] !== $input['id'];
            }
        );
        file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
        echo json_encode(["message" => "Product removed from wishlist"]);
    }
}