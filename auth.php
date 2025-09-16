<?php
require_once 'db.php';

function registerUser(string $name, string $email, string $password, string $role = 'user'): array
{
    $errors = [];

    if (!in_array($role, ['user', 'admin'])) {
        $errors['role'] = 'Invalid role specified.';
        return $errors;
    }

    $stmt = db()->prepare("SELECT id FROM users WHERE email = ? OR name = ?");
    $stmt->execute([$email, $name]);

    if ($stmt->fetch()) {
        $errors['general'] = 'Email or username already exists.';
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user with specified role
        $stmt = db()->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hashedPassword, $role]);

        return [
            'id' => db()->lastInsertId(),
            'name' => $name,
            'email' => $email,
            'role' => $role
        ];
    }

    return $errors;
}

function loginUser(string $login, string $password): ?array
{
    $stmt = db()->prepare("SELECT * FROM users WHERE email = ? OR name = ?");
    $stmt->execute([$login, $login]);
    $userRecord = $stmt->fetch();

    if ($userRecord && password_verify($password, $userRecord['password'])) {
        return [
            'id' => $userRecord['id'],
            'name' => $userRecord['name'],
            'email' => $userRecord['email'],
            'role' => $userRecord['role']
        ];
    }

    return null;
}

function getUsers ($searchTerm = ''){
    if ($searchTerm !== '') {
        $stmt = db() -> prepare("SELECT * FROM users WHERE name LIKE ? OR email LIKE ?");
        $searchTerm = "%$searchTerm%";
        $stmt -> execute([$searchTerm, $searchTerm]);
    } else {
        return [];
    }

    return $stmt -> fetchAll(PDO::FETCH_ASSOC);
}

function deleteUser ($id):bool {
    $stmt = db() -> prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$id]);
}

function updateUser($id, $name, $email, $role) {
        $stmt = db() -> prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
        return $stmt -> execute([$name, $email, $role, $id]);
    }