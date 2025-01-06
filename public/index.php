<?php

// paths
$dirRoot = realpath(__DIR__ . '/../');
$databasePath = realpath(__DIR__ . '/../ users.db');

// database connection
try {
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to connect to the database: '
            . $e->getMessage()
    ]);
    exit;
}

// server variables
$method = $_SERVER['REQUEST_METHOD'];
$uri = trim($_SERVER['REQUEST_URI'], '/');

// function for error sending
function sendError($message, $statusCode = 400)
{
    http_response_code($statusCode);
    echo json_encode(['error' => $message]);
    exit;
}

// connecting and creating object of UserModel
require_once "$dirRoot" . '/src/UserModel.php';
$userModel = new UserModel($pdo);

// routing
if ($method === 'GET' && $uri === 'users') {
    try {
        $users = $userModel->getAllUsers();
        if (empty($users)) {
            sendError('No users found.', 404);
        }
        http_response_code(200);
        echo json_encode($users);
    } catch (Exception $e) {
        sendError($e->getMessage(), 500);
    }
} elseif ($method === 'POST' && $uri === 'users') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Checking if we have nessecary data
    if (!isset($data['name']) || !isset($data['email'])) {
        sendError('Name and email are required.');
    }

    // basic validation
    if (strlen($data['name']) < 2) {
        sendError('Name must be at least 2 symbols long.');
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        sendError('Email must be a valid e-mail.');
    }

    try {
        $userModel->createUser($data['name'], $data['email']);
        http_response_code(201);
        echo json_encode(['message' => 'User was created seccessfully.']);
    } catch (Exception $e) {
        sendError($e->getMessage(), 500);
    }
} elseif ($method === 'PUT' && preg_match('/users\/(\d+)/', $uri, $matches)) {
    $id = (int)$matches[1];
    $data = json_decode(file_get_contents('php://input'), true);

    // validation
    if (!isset($data['name']) || !isset($data['email'])) {
        sendError('Name and email are required.');
    }

    if (strlen($data['name']) < 2) {
        sendError('Name must be at least 2 symbols long.');
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        sendError('Email must be a valid e-mail.');
    }

    try {
        $result = $userModel->updateUser($id, $data['name'], $data['email']);
        if ($result === 0) {
            sendError("User not found.", 404);
        }

        http_response_code(200);
        echo json_encode(['message' => 'User updated successfully']);
    } catch (Exception $e) {
        sendError($e->getMessage(), 500);
    }
} elseif ($method === 'DELETE' && preg_match('/users\/(\d+)/', $uri, $matches)) {
    $id = (int)$matches[1];

    try {
        $result = $userModel->deleteUser($id);
        if ($result === 0) {
            sendError('User not found.', 404);
        }

        http_response_code(200);
        echo json_encode(['message' => 'User deleted successfully.']);
    } catch (Exception $e) {
        sendError($e->getMessage(), 500);
    }
} else {
    sendError('Route not found.', 404);
}
