<?php

namespace Arsen\ExrPhpRest;

class UserModel
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllUsers(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users');
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function createUser(string $name, string $email): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, created_at)
            VALUES (:name, :email, :created_at)'
        );
        $stmt->execute([
            ":name" => $name,
            ":email" => $email,
            ":created_at" => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    public function updateUser(int $id, string $name, string $email): int
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users
            SET name = :name, email = :email
            WHERE id = :id'
        );
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':id' => $id,
        ]);

        return $stmt->rowCount();
    }

    public function deleteUser(int $id): int
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM users
                WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount();
    }
}
