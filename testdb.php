<?php
$conn = mysqli_connect('localhost', 'root', 'usbw', 'citylink', 3306);
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}
echo 'Connected successfully!';
mysqli_close($conn);
