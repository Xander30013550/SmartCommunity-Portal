<?php
//  This script securely logs out the current user by clearing the session and redirecting them to the homepage
//  (`index.php`). It ensures all session data is removed and the session is properly terminated.
session_start();
session_unset();
session_destroy();
header('Location: index.php');
exit;