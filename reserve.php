<?php
declare(strict_types=1);
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');


// Make sure user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in.']);
    exit;
}

// Grab POST data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$amount = (int) ($_POST['amount'] ?? 0);
$eventName = trim($_POST['eventName'] ?? '');
$eventTime = trim($_POST['eventTime'] ?? '');
$eventLocation = trim($_POST['eventLocation'] ?? '');

// Basic validation
if ($name === '' || $email === '' || $amount <= 0 || $eventName === '' || $eventTime === '' || $eventLocation === '') {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
    exit;
}

// Connect to MySQL using mysqli (like your login page)
$conn = mysqli_connect('localhost', 'root', 'usbw', 'citylink', 3306);

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
}

// Escape values
$nameEsc = mysqli_real_escape_string($conn, $name);
$emailEsc = mysqli_real_escape_string($conn, $email);
$eventNameEsc = mysqli_real_escape_string($conn, $eventName);
$eventTimeEsc = mysqli_real_escape_string($conn, $eventTime);
$eventLocationEsc = mysqli_real_escape_string($conn, $eventLocation);
$userId = (int) ($_SESSION['user']['id'] ?? 0);

// Insert reservation
$sql = "INSERT INTO reservations (user_id, AmountOfPeople, EventName, EventTime, EventLocation, CreatedAt) 
        VALUES ($userId, $amount, '$eventNameEsc', '$eventTimeEsc', '$eventLocationEsc', NOW())";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true, 'message' => 'Reservation successfully submitted!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
