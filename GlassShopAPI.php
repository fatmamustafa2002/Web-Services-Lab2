<?php

require_once 'Resources/MySQLHandler.php';


header('Content-Type: application/json');


try {
    $db = new MySQLHandler();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'internal server error!']);
    exit;
}


$method = $_SERVER['REQUEST_METHOD'];
$url_pieces = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$resource = isset($url_pieces[1]) ? $url_pieces[1] : '';
$resource_id = isset($url_pieces[2]) && is_numeric($url_pieces[2]) ? (int)$url_pieces[2] : 0;


$allowed_methods = ['GET', 'POST', 'PUT', 'DELETE'];


if (!in_array($method, $allowed_methods)) {
    http_response_code(405);
    echo json_encode(['error' => 'method not allowed!']);
    exit;
}


if ($resource !== 'items') {
    http_response_code(404);
    echo json_encode(['error' => 'Resource doesn\'t exist']);
    exit;
}


$valid_columns = [
    'id', 'product_name', 'product_code', 'photo', 'list_price',
    'reorder_level', 'units_in_stock', 'category', 'country',
    'rating', 'discontinued', 'date'
];


switch ($method) {
    case 'GET':
        if ($resource_id === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Resource doesn\'t exist']);
            exit;
        }
        $item = $db->select('items', ['id' => $resource_id]);
        if ($item) {
            http_response_code(200);
            echo json_encode($item[0]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Resource doesn\'t exist']);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad request']);
            exit;
        }
       
        foreach (array_keys($input) as $key) {
            if (!in_array($key, $valid_columns)) {
                http_response_code(400);
                echo json_encode(['error' => 'Bad request']);
                exit;
            }
        }
       
        if (!isset($input['id']) || !isset($input['product_name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad request']);
            exit;
        }
        
        if ($db->insert('items', $input)) {
            http_response_code(201);
            echo json_encode(['status' => 'Resource was added successfully!']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'internal server error!']);
        }
        break;

    case 'PUT':
        if ($resource_id === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Resource not found!']);
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad request']);
            exit;
        }
       
        foreach (array_keys($input) as $key) {
            if (!in_array($key, $valid_columns)) {
                http_response_code(400);
                echo json_encode(['error' => 'Bad request']);
                exit;
            }
        }
        
        $exists = $db->select('items', ['id' => $resource_id]);
        if (!$exists) {
            http_response_code(404);
            echo json_encode(['error' => 'Resource not found!']);
            exit;
        }
        
        if ($db->update('items', $input, ['id' => $resource_id])) {
            $updated = $db->select('items', ['id' => $resource_id]);
            http_response_code(200);
            echo json_encode($updated[0]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'internal server error!']);
        }
        break;

    case 'DELETE':
        if ($resource_id === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Resource not found!']);
            exit;
        }
        
        $exists = $db->select('items', ['id' => $resource_id]);
        if (!$exists) {
            http_response_code(404);
            echo json_encode(['error' => 'Resource not found!']);
            exit;
        }
        
        if ($db->delete('items', ['id' => $resource_id])) {
            http_response_code(200);
            echo json_encode(['status' => 'Resource was deleted successfully!']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'internal server error!']);
        }
        break;
}
?>