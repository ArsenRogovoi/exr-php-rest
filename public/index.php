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

if ($method === 'GET' && $uri === 'users') {
    try {
        $stmt = $pdo->prepare('SELECT * FROM users');
        $stmt->execute();

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($users) {
            http_response_code(200);
            echo json_encode($users);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'No users found.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST' && $uri === 'users') {
    // Checking if we have nessecary data
    if (!isset($_POST['name']) || !isset($_POST['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and email are required.']);
        exit;
    }

    // save data
    $name = $_POST['name'];
    $email = $_POST['email'];

    // basic validation
    if (strlen($name) < 2) {
        http_response_code(400);
        echo json_encode(['error' => 'Name must be at least 2 symbols long.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email must be a valid e-mail.']);
        exit;
    }

    // writting in database
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO users (name, email, created_at)
            VALUES (:name, :email, :created_at)'
        );
        $stmt->execute([
            ":name" => $name,
            ":email" => $email,
            ":created_at" => date('Y-m-d H:i:s'),
        ]);

        http_response_code(201); //201 Created
        echo json_encode(['message' => 'User was created successfully.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
} elseif ($method === 'PUT' && preg_match('/users\/(\d+)/', $uri, $matches)) {
    $id = (int)$matches[1];
    $data = json_decode(file_get_contents('php://input'), true);

    // validation
    if (!isset($data['name']) || !isset($data['email'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and email are required.']);
        exit;
    }

    $name = $data['name'];
    $email = $data['email'];

    // basic validation
    if (strlen($name) < 2) {
        http_response_code(400);
        echo json_encode(['error' => 'Name must be at least 2 symbols long.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email must be a valid e-mail.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'UPDATE users
            SET name = :name, email = :email
            WHERE id = :id'
        );
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':id' => $id,
        ]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit;
        }

        http_response_code(200);
        echo json_encode(['message' => 'User updated successfully.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($method === 'DELETE' && preg_match('/users\/(\d+)/', $uri, $matches)) {
    $id = (int)$matches[1];

    try {
        $stmt = $pdo->prepare(
            'DELETE FROM users
            WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found.']);
            exit;
        }

        http_response_code(200);
        echo json_encode(['message' => 'User deleted successfully.']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(404);
    echo "Route not found. $uri";
}
