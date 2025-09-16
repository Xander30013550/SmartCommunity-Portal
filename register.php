<?php
require_once 'functions.php';
require_once 'auth.php';

// Check if already logged in
session_start();
if (isset($_SESSION['user'])) {
    if ($_SESSION['user']['role'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: user_home.php');
    }
    exit;
}

$menusPath = __DIR__ . '/config/menus.xml';
$menuItems = getPrimaryMenuItems($menusPath);
$current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: 'index.php');

$errors = [];
$name = $email = $password = $confirm_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

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

    // If no errors, register the user
    if (empty($errors)) {
        $user = registerUser($name, $email, $password);
        if (isset($user['id'])) {
            $_SESSION['user'] = $user;
            header('Location: index.php');
            exit;
        } else {
            $errors = $user;
        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register - Smart Community Portal</title>
    <link rel="stylesheet" href="../styles/styles.css" />
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />
</head>

<!--    Main Content      -->

<body class="sb-expanded">

    <!--    Page Content      -->

    <nav id="sidebar">
        <ul>
            <li>
                <button onclick="toggleSidebar()" id="toggle-btn" aria-label="Toggle sidebar">
                    <i id="icon-expand" class="bx bx-chevrons-right hidden"></i>
                    <i id="icon-collapse" class="bx bx-chevrons-left"></i>
                </button>
            </li>
            <?php foreach ($menuItems as $item):
                $target = basename(parse_url($item['url'], PHP_URL_PATH) ?: '');
                $isActive = $target === $current || ($target === '' && $current === 'index.php');
                ?>
                <li class="<?= $isActive ? 'active' : '' ?>">
                    <a href="<?= e($item['url']) ?>">
                        <i class="<?= e($item['icon']) ?>"></i>
                        <span><?= e($item['label']) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <main>
        <?php if (!empty($errors['general'])): ?>
            <div class="error"><?= e($errors['general']) ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate>
            <img src="../images/CityLinkIcon.png" width="33%" style="margin: auto;" alt="Logo" />

            <h1>Register</h1>

            <label for="name">Username</label>
            <input type="text" id="name" name="name" value="<?php echo e($name); ?>" required />
            <?php if (!empty($errors['name'])): ?>
                <div class="error"><?= e($errors['name']) ?></div>
            <?php endif; ?>

            <label for="email">Email address</label>
            <input type="email" id="email" name="email" value="<?php echo $email; ?>" required />
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

            <button type="submit">Register</button>
            <p>Already registered? <a href="login.php">Login here</a>.</p>
        </form>
    </main> <!--    End Page Content      -->

    <!--    Footer section      -->
    <footer>
        &copy; 2025 CityLink Initiatives.
        <a href="privacy.php">Privacy Policy</a>
    </footer>

    <script type="text/javascript" src="../js/script.js" defer></script>
</body>

</html>