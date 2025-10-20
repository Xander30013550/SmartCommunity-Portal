<?php
//  This PHP script attempts to connect to a MySQL database using `mysqli_connect` with
//  given credentials and database details. It outputs a success message if the connection
//  works or terminates with an error message if the connection fails, then closes the connection.
$conn = mysqli_connect('localhost', 'root', 'usbw', 'citylink', 3306);
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
echo 'Connected successfully!';
mysqli_close($conn);