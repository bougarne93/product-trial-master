<?php
require_once __DIR__ . '/../classes/Product.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../config.php';

$data = json_decode(file_get_contents(DB_FILE), true);
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

if (preg_match('#^/products(?:/([0-9]+))?$#', $uri, $matches)) {
    $id = $matches[1] ?? null;

    if ($method === 'GET') {
        $headers = getallheaders();
        $token = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        $token = str_replace('Bearer ', '', $token);
        $user_email = Auth::verifyJWT($token);

        if (!$user_email) {
            http_response_code(401);
            echo json_encode(["error" => "Unauthorized"]);
            exit;
        }

        if ($id !== null) {
            foreach ($data['products'] as $product) {
                if ($product['id'] == $id) {
                    echo json_encode($product);
                    exit;
                }
            }
            http_response_code(404);
            echo json_encode(["error" => "Product not found"]);
        } else {
            echo json_encode($data['products']);
        }

    } elseif ($method === 'POST') {
        $headers = getallheaders();
        $token = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        $token = str_replace('Bearer ', '', $token);
        $user_email = Auth::verifyJWT($token);
        if (!$user_email || $user_email !== 'admin@admin.com') {
            http_response_code(403);
            echo json_encode(["error" => "Forbidden"]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $input['id'] = time();
        $input['createdAt'] = time();
        $input['updatedAt'] = time();
        $data['products'][] = $input;
        file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
        echo json_encode($input);

    } elseif ($method === 'PATCH' && $id !== null) {
        $headers = getallheaders();
        $token = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        $token = str_replace('Bearer ', '', $token);
        $user_email = Auth::verifyJWT($token);
        if (!$user_email || $user_email !== 'admin@admin.com') {
            http_response_code(403);
            echo json_encode(["error" => "Forbidden"]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $found = false;
        foreach ($data['products'] as &$product) {
            if ($product['id'] == $id) {
                foreach ($input as $key => $value) {
                    $product[$key] = $value;
                }
                $product['updatedAt'] = time();
                $found = true;
                break;
            }
        }
        if ($found) {
            file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(["message" => "Product updated"]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Product not found"]);
        }

    } elseif ($method === 'DELETE' && $id !== null) {
        $headers = getallheaders();
        $token = isset($headers['Authorization']) ?  $headers['Authorization'] : '';
        $token = str_replace('Bearer ', '', $token);
        $user_email = Auth::verifyJWT($token);
        if (!$user_email || $user_email !== 'admin@admin.com') {
            http_response_code(403);
            echo json_encode(["error" => "Forbidden"]);
            exit;
        }

        $initialCount = count($data['products']);
        $data['products'] = array_filter($data['products'], function ($p) use ($id) {
            return $p['id'] != $id;
        });
        if (count($data['products']) !== $initialCount) {
            file_put_contents(DB_FILE, json_encode($data, JSON_PRETTY_PRINT));
            echo json_encode(["message" => "Product deleted"]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Product not found"]);
        }

    } else {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
    }
}