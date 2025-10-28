<?php
//  This PHP script handles an AJAX reservation form submission by first checking if the user is
//  logged in via session. It validates input fields, connects securely to a MySQL database, 
//  escapes user inputs to prevent SQL injection, inserts a new reservation record into the 
//  `reservations` table, and returns a JSON response indicating success or failure.
declare(strict_types=1);
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

function fail(string $msg, int $http = 400, array $meta = []): never {
    http_response_code($http);
    error_log('[reserve.php] ' . $msg . (empty($meta) ? '' : ' | ' . json_encode($meta)));
    echo json_encode(['success' => false, 'message' => $msg], JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    if (!isset($_SESSION['user']['id'])) {
        fail('You must be logged in.', 401);
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        fail('Invalid method', 405);
    }

    $name    = trim((string)($_POST['name'] ?? ''));
    $email   = trim((string)($_POST['email'] ?? ''));
    $amount  = (int)($_POST['amount'] ?? 0);
    $eventId = trim((string)($_POST['event_id'] ?? ''));

    if ($name === '')  fail('Name is required.');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('A valid email is required.');
    if ($amount < 1)  fail('Amount must be at least 1.');
    if ($eventId === '') fail('Please select an event before reserving.');

    $pdo = db(); // your PDO helper should set ERRMODE_EXCEPTION

    // Get canonical event details
    $ev = $pdo->prepare("SELECT id, title, date_info AS date, location FROM events WHERE id = :id");
    $ev->execute([':id' => $eventId]);
    $event = $ev->fetch(PDO::FETCH_ASSOC);
    if (!$event) {
        fail('Selected event was not found.', 404, ['event_id' => $eventId]);
    }

    // Insert into your reservations table. If your schema uses different names, adjust here.
    $ins = $pdo->prepare(
        "INSERT INTO reservations ( event_id, name, email, amount, created_at)
         VALUES ( :event_id, :name, :email, :amount, NOW())"
    );
    $ins->execute([
        ':event_id' => $event['id'],
        ':name'     => $name,
        ':email'    => $email,
        ':amount'   => $amount,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Reservation submitted. See you there!',
        'reservation' => [
            'event_id' => $event['id'],
            'name'     => $name,
            'email'    => $email,
            'amount'   => $amount
        ],
    ], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('[reserve.php] ' . get_class($e) . ': ' . $e->getMessage());
    fail('Server error while saving your reservation.', 500);
}
