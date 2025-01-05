<?php

try {
    $pdo = new PDO('sqlite: users.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo 'Connection to the database was successful.\n';
} catch (PDOException $e) {
    echo 'Database connection error: ' . $e->getMessage() . '\n';
    exit;
}

$sql = 'CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    created_at TEXT
    )';

try {
    $pdo->exec($sql);
    echo 'Table "users" has been successfully created or already exists.\n';
} catch (PDOException $e) {
    echo 'Error occured in try to create table: ' . $e->getMessage() . '\n';
}

echo 'Script DB initialization was successfully executed.\n';
