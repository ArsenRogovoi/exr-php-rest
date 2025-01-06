<?php

// paths
$dirRoot = realpath(__DIR__ . '/../');
$databasePath = realpath(__DIR__ . '/../ users.db');

require_once "$dirRoot" . '/autoload.php';

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

// function for error sending
function sendError($message, $statusCode = 400)
{
    http_response_code($statusCode);
    echo json_encode(['error' => $message]);
    exit;
}

// connecting and creating object of UserModel
$userModel = new UserModel($pdo);

// creating and configurating router
$router = new Router();

$router->addRoute('GET', 'users', function () use ($userModel) {
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
});
$router->addRoute('POST', 'users', function () use ($userModel) {
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
});
$router->addRoute('PUT', 'users/{id}', function ($id) use ($userModel) {
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
});
$router->addRoute('DELETE', 'users/{id}', function ($id) use ($userModel) {
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
});

$router->dispatch($_SERVER['REQUEST_METHOD'], trim($_SERVER['REQUEST_URI'], '/'));
