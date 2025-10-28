<?php
require_once 'config.php';

//  This function establishes and returns a reusable PDO database connection using configured constants,
//  with error handling and default fetch mode set to associative arrays. It uses a static variable to 
//  ensure the connection is only created once per request.
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            die("Could not connect to the database: " . $e->getMessage());
        }
    }
    return $pdo;
}