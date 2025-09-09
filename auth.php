<?php
require_once 'db.php';

function registerUser(string $name, string $email, string $password): array {
    $errors = [];

    $stmt = db()->prepare("SELECT id FROM users WHERE email = ? OR name = ?");
    $stmt->execute([$email, $name]);
    if ($stmt->fetch()) {
        $errors['general'] = 'Email or username already exists.';
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $role = 'user'; 

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

function loginUser(string $login, string $password): ?array {
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