<?php
$path = realpath(__DIR__ . '/../db.php');
require_once $path;

function addFeedbackToTable (string $name, string $email, string $subject, string $message): array {
    $errors = [];

    if (empty($name)){
        $errors['name'] = 'Name is required.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors['email'] = 'A valid email is required.';
    }

    if (empty($message)) {
        $errors['message'] = 'Message is required.';
    }

    if (!empty($errors)) {
        return $errors;
    }

    if (empty($errors)){
        $stmt = db()->prepare("INSERT INTO feedback (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $email, $subject, $message]);

        return [
            'id' => db()->lastInsertId(),
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message
        ];
    }

    return $errors;
}