<?php
$path = realpath(__DIR__ . '/../db.php');
require_once $path;

//  This function validates user input for a feedback form and, if no errors are found, inserts the data into a database. 
//  If validation fails or the database operation is unsuccessful, it returns an array of error messages.
function addFeedbackToTable (string $name, string $email, string $subject, string $message): array {
    file_put_contents(__DIR__ . '/../debug_entered.txt', "Entered addFeedbackToTable()\n", FILE_APPEND);
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

        if (!$stmt->execute([$name, $email, $subject, $message])) {
            $errors['sql'] = 'Database insertion failed: ' . implode(', ', $stmt->errorInfo());
            return $errors;
        }

        return [
            'id' => db()->lastInsertId(),
            'name' => $name,
            'email' => $email,
            'subject' => $subject,
            'message' => $message
        ];
    }

    file_put_contents(__DIR__ . '/../debug_entered.txt', "Exiting addFeedbackToTable(): " . print_r($errors ?: 'success', true) . "\n", FILE_APPEND);

    return $errors;
}

function getFeedbackFromTable(int $limit = 10, int $offset = 0): array {
    // Prepare the SQL query to fetch feedback with pagination
    $sql = "SELECT id, name, email, subject, message, created_at FROM feedback ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    
    // Prepare the statement
    $stmt = db()->prepare($sql);
    
    // Bind parameters
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    // Execute the query
    $stmt->execute();
    
    // Fetch all rows
    $feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return the feedback data
    return $feedbacks;
}