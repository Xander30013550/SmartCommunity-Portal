<?php
declare(strict_types=1);

session_start();
libxml_use_internal_errors(true);

require_once 'functions.php';
require_once 'auth.php';
require_once __DIR__ . '/vendor/autoload.php';

use App\Menu\MenuRepository;
use App\Menu\NavRenderer;

// --- Ensure user is logged in and is an admin ---
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Redirect general users elsewhere
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: user_home.php');
    exit;
}

$user = $_SESSION['user'];

// --- Handle logout ---
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// --- App boot ---
date_default_timezone_set('Australia/Perth');

$menuRepo = new MenuRepository(__DIR__ . '/config');
$nav = new NavRenderer($menuRepo);
$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'admin.php');

$errors = [];
$successMessage = '';  // Variable for success message
$name = $email = $password = $confirm_password = '';
$role = '';

// Check if the user is an admin
$isAdmin = $user['role'] === 'admin';

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['register'] )) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $role = $isAdmin ? ($_POST['role'] ?? '') : 'user';

    // Validation
    if ($name === '') {
        $errors['name'] = 'Name is required.';
    }

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }

    if ($confirm_password === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }

    if ($role === '') {
        $errors['role'] = 'Role is required.';
    } elseif ($isAdmin && !in_array($role, ['user', 'admin'])) {
        $errors['role'] = 'Invalid role. Only "user" or "admin" are allowed.';
    }

    // If no errors, register the user
    if (empty($errors)) {
        $user = registerUser($name, $email, $password, $role);
        if (isset($user['id'])) {
            // Set success message if user is registered successfully
            $successMessage = "User '{$name}' has been successfully registered with the role '{$role}'.";
        } else {
            // In case of failure, assign the error messages
            $errors = $user;
        }
    }
}

$users = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchTerm = trim($_POST['search_term'] ?? '');
    
    if ($searchTerm === '') {
        $errors['search'] = 'Please enter a name or email to search.';
    }

    if (empty($errors)) {
        // Perform the search query
        $users = getUsers($searchTerm); // Fetch matching users
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['search'])) {
    $users = getUsers();
}

//  Delete user
if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    $deleteResult = deleteUser($deleteId);

    if ($deleteResult) {
        echo "<div class='success'>User has been deleted successfully.</div>";
    } else {
        echo "<div class='error'>There was an error deleting the user.</div>";
    }
}
?>

<!DOCTYPE html>

<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>User Management - Smart Community Portal</title>
        <link rel="stylesheet" href="./styles/styles.css" />
        <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
    </head>

    <body class="sb-expanded">
        <?= $nav->render($current) ?>

        <main>
            <section>
                <h1 class="page-title">Admin Portal - Add New User</h1>

                <div class="user-info" style="margin-bottom: 1em; font-size: 1rem;">
                    Logged in as <strong><?= htmlspecialchars($user['name']) ?></strong><br>
                    <a href="admin.php">Return to Dashboard</a>
                    &nbsp;|&nbsp; <a href="#">Edit Announcements</a>
                    &nbsp;|&nbsp; <a href="?logout=true">Logout</a>
                </div>

                <div style="display: flex; width:100%;">
                    <!--    Add user section    -->
                    <div style="flex: 1; padding: 10px;">
                        <h2> Add a New User </h2>

                        <form method="POST" action="" novalidate style="width: 99%;">
                            <label for="name">Username</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required />
                            <?php if (!empty($errors['name'])): ?>
                                <div class="error"><?= e($errors['name']) ?></div>
                            <?php endif; ?>

                            <label for="email">Email address</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required />
                            <?php if (!empty($errors['email'])): ?>
                                <div class="error"><?= e($errors['email']) ?></div>
                            <?php endif; ?>

                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required minlength="6" />
                            <?php if (!empty($errors['password'])): ?>
                                <div class="error"><?= e($errors['password']) ?></div>
                            <?php endif; ?>

                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6" />
                            <?php if (!empty($errors['confirm_password'])): ?>
                                <div class="error"><?= e($errors['confirm_password']) ?></div>
                            <?php endif; ?>

                            <?php if ($isAdmin): ?>
                                <label for="role">Set Their Role</label>
                                <select id="role" name="role" required>
                                    <option value="user" <?= ($role === 'user') ? 'selected' : '' ?>>user</option>
                                    <option value="admin" <?= ($role === 'admin') ? 'selected' : '' ?>>admin</option>
                                </select>
                                <?php if (!empty($errors['role'])): ?>
                                    <div class="error"><?= e($errors['role']) ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <input type="hidden" name="role" value="user" />
                            <?php endif; ?>

                            <button type="submit" name="register">Register</button>
                        </form><br>

                        <?php if ($successMessage): ?>
                            <div class="success"><?= htmlspecialchars($successMessage) ?></div>
                        <?php elseif (!empty($errors['general'])): ?>
                            <div class="error"><?= e($errors['general']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!--    Search record section-->
                    <div style="flex: 1; padding: 10px;">
                        <h2> View Users Details </h2>

                        <!--    Search form     -->
                        <form method="post" action="" style="width:99%;">
                            <label for="search_term">Search by Name or Email:</label>
                            <input type="text" id="search_term" name="search_term" value="<?= htmlspecialchars($searchTerm ?? '') ?>" />
                            <button type="submit" name="search">Search</button>
                        </form><br><br>

                        <!-- Display Errors if Any -->
                        <?php if (!empty($errors['search'])): ?>
                            <div class="error"><?= e($errors['search']) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($users)): ?>
                            <ul>
                                <?php foreach ($users as $user): ?>
                                    <li>
                                        <strong>Name:</strong> <?= htmlspecialchars($user['name']) ?><br>
                                        <strong>Email:</strong> <?= htmlspecialchars($user['email']) ?><br>
                                        <strong>Role:</strong> <?= htmlspecialchars($user['role']) ?><br>
                                        <strong>ID:</strong> <?= $user['id'] ?><br>
                                    </li><br>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No users found.</p>
                        <?php endif; ?>
                    </div>

                    <div style="flex: 1; padding: 10px;">
                        <h2>Update Users Details</h2>
                    </div>

                    <div style="flex: 1; padding: 10px;">
                        <h2>Delete User</h2>

                        <form method="POST" action="" style="width:99%;">
                            <label for="user_id">Enter User ID to Delete</label>
                            <input type="number" id="user_id" name="user_id" required />
                            
                            <button type="submit" name="delete_user">Delete User</button>
                        </form>

                        <?php
                            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
                                $userId = (int)$_POST['user_id'];
                                $deleteResult = deleteUser($userId);

                                if ($deleteResult) {
                                    echo "<div class='success'>User with ID {$userId} has been deleted successfully.</div>";
                                } else {
                                    echo "<div class='error'>There was an error deleting the user. Please check the ID and try again.</div>";
                                }
                            }
                        ?>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            &copy; 2025 CityLink Initiatives.
            <a href="privacy.php">Privacy Policy</a>
        </footer>
    </body>
</html>