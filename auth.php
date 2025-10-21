<?php
require_once 'db.php';

//  This function registers a new user with a name, email, password, and role (default 'user'), checking 
//  for duplicates before hashing the password and inserting into the database, returning the new user 
//  data on success or error messages on failure.
function registerUser(string $name, string $email, string $password, string $role = 'user'): array {
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

//  This function attempts to log in a user by checking if the provided login (email or username)
//  exists and if the password matches the hashed password, returning the user data on success or 
//  null on failure.
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

//  This function fetches users from the database whose name or email 
//  partially matches the given search term, returning an empty array
//  if no search term is provided.
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

//  This function deletes a user by their ID from the database and returns 
//  true if successful or false otherwise.
function deleteUser ($id):bool {
    $stmt = db() -> prepare("DELETE FROM users WHERE id = ?");
    return $stmt->execute([$id]);
}

//  This function updates a user's name, email, and role in the database based
//  on their ID, returning true if the update succeeds.
function updateUser($id, $name, $email, $role) {
    $stmt = db() -> prepare("UPDATE users SET name = ?, email = ?, role = ? WHERE id = ?");
    return $stmt -> execute([$name, $email, $role, $id]);
}